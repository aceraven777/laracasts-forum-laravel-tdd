<template>
	<div :id="'reply-'+id" class="panel" :class="isBest ? 'panel-success' : 'panel-default'">
		<div class="panel-heading">
			<div class="level">
				<h5 class='flex'>
					<a :href="'/profiles/'+data.owner.name" v-text="data.owner.name">
					</a>
					said <span v-text="ago"></span>
				</h5>

				<div v-if="signedIn">
					<favorite :reply="data"></favorite>
				</div>
			</div>
		</div>

		<div class="panel-body">
			<div v-if="editing">
				<form @submit.prevent="update">
					<div class="form-group">
						<textarea class='form-control' v-model="formattedBody" required></textarea>
					</div>

					<button class="btn btn-xs btn-primary" type="submit">Update</button>
					<button class="btn btns-xs btn-link" @click="editing = false" type="button">Cancel</button>
				</form>
			</div>
			<div v-else v-html="body"></div>
		</div>
		
		<div class="panel-footer level">
			<div v-if="authorize('updateReply', reply)">
				<button class="btn btn-xs mr-1" @click="editing = true">Edit</button>
				<button class="btn btn-xs btn-danger mr-1" @click="destroy">Delete</button>
			</div>

			<button class="btn btn-xs btn-default ml-a" @click="markBestReply" v-show="! isBest">Best Reply?</button>
		</div>
	</div>
</template>

<script>
    import Favorite from './Favorite.vue';
	import moment from 'moment';

    export default {
        props: ['data', 'bestReply'],

        components: { Favorite },

        data() {
            return {
                editing: false,
                id: this.data.id,
                body: this.data.body,
                isBest: this.data.isBest,
				reply: this.data,
            };
        },

		computed: {
			ago() {
				return moment(this.data.created_at).fromNow() + '...';
			},

			formattedBody: {
				get: function () {
					return this.$options.filters.striphtml(this.body);
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

				$('#reply-' + this.data.id + ' form textarea').atwho({
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
                axios.patch('/replies/' + this.data.id, {
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
                axios.delete('/replies/' + this.data.id).then(() => {
					this.$emit('deleted', this.data.id);
				});
            },

            favorite() {
                axios.post('/replies/' + this.data.id + '/favorites').then(() => {
					flash('The reply has been favorited.');
				});
            },

			markBestReply() {
				axios.post('/replies/' + this.data.id + '/best').then(() => {
					window.events.$emit('best-reply-selected', this.data.id);
				}).catch(function (error) {
					console.log('error');
					console.log(error);
					if (error.response.status == 403) {
						flash('Your action is unauthorized.');
						return;
					}

					flash('An error has occurred.');
				});
			},
        },
    }
</script>