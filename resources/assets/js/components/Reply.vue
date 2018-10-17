<template>
	<div :id="'reply-'+id" class="panel" :class="isBest ? 'panel-success' : 'panel-default'">
		<div class="panel-heading">
			<div class="level">
				<h5 class='flex'>
					<a :href="'/profiles/'+reply.owner.name" v-text="reply.owner.name">
					</a>
					said <span v-text="ago"></span>
				</h5>

				<div v-if="signedIn">
					<favorite :reply="reply"></favorite>
				</div>
			</div>
		</div>

		<div class="panel-body">
			<div v-if="editing">
				<form @submit.prevent="update">
					<div class="form-group">
						<wysiwyg v-model="formattedBody"></wysiwyg>
					</div>

					<button class="btn btn-xs btn-primary" type="submit">Update</button>
					<button class="btn btns-xs btn-link" @click="editing = false" type="button">Cancel</button>
				</form>
			</div>
			<div v-else v-html="body"></div>
		</div>
		
		<div class="panel-footer level" v-if="authorize('owns', reply) || authorize('owns', reply.thread)">
			<div v-if="authorize('owns', reply)">
				<button class="btn btn-xs mr-1" @click="editing = true">Edit</button>
				<button class="btn btn-xs btn-danger mr-1" @click="destroy">Delete</button>
			</div>

			<button class="btn btn-xs btn-default ml-a" @click="markBestReply" v-if="authorize('owns', reply.thread)">Best Reply?</button>
		</div>
	</div>
</template>

<script>
    import Favorite from './Favorite.vue';
	import moment from 'moment';

    export default {
        props: ['reply'],

        components: { Favorite },

        data() {
            return {
                editing: false,
                id: this.reply.id,
                body: this.reply.body,
                isBest: this.reply.isBest,
            };
        },

		computed: {
			ago() {
				return moment(this.reply.created_at).fromNow() + '...';
			},

			formattedBody: {
				get: function () {
					return this.body;
					// return this.$options.filters.striphtml(this.body);
				},

				set: function (newValue) {
					this.body = newValue;
				}
			},
		},

		created() {
			window.events.$on('best-reply-selected', (id) => {
				this.isBest = (id === this.id);
			});
		},

		updated() {
			if (this.editing) {
				var component = this;

				$('#reply-' + this.id + ' form textarea').atwho({
					at: "@",
					delay: 750,
					callbacks: {
						remoteFilter: function(query, callback) {
							if (! query) {
								return;
							}

							$.getJSON("/api/users", {name: query}, function(usernames) {
								callback(usernames);
							});
						}
					}
				})
				.on('inserted.atwho', function (event, flag, query) {
					component.formattedBody = $(this).val();
				});
			}
        },

        methods: {
            update() {
                axios.patch('/replies/' + this.id, {
                    body: this.body,
                })
				.catch((error) => {
                    flash(error.response.data, 'danger');
                })
				.then(({data}) => {
					this.editing = false;

                	flash('Updated!');
				});
            },

            destroy() {
                axios.delete('/replies/' + this.id).then(() => {
					this.$emit('deleted', this.id);
				});
            },

            favorite() {
                axios.post('/replies/' + this.id + '/favorites').then(() => {
					flash('The reply has been favorited.');
				});
            },

			markBestReply() {
				axios.post('/replies/' + this.id + '/best').then(() => {
					window.events.$emit('best-reply-selected', this.id);
				}).catch(function (error) {
					if (error.response.status == 403) {
						flash('Your action is unauthorized.', 'danger');
						return;
					}

					flash('An error has occurred.', 'danger');
				});
			},
        },
    }
</script>