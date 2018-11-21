<script>
    import Replies from '../components/Replies.vue';
    import SubscribeButton from '../components/SubscribeButton.vue';

    export default {
        props: ['thread'],

        components: { Replies, SubscribeButton },

        data() {
            return {
                repliesCount: this.thread.replies_count,
                locked: this.thread.locked,
                editing: false,
                title: this.thread.title,
                body: this.thread.body,
                form: {},
            };
        },

        created () {
            this.resetForm();
        },

        mounted () {
            this.highlight(this.$refs.question);
        },

        watch: {
            editing() {
                if (!this.editing) {
                    setTimeout(() => this.highlight(this.$refs.question), 100);
                }
            }
        },

        methods: {
            toggleLock() {
                let uri = '/locked-threads/' + this.thread.slug;
                let method = (this.locked ? 'delete' : 'post');
                
                axios[method](uri)
				.catch((error) => {
                    flash(error.response.data, 'danger');
                })
				.then(({data}) => {
					this.locked = ! this.locked;
				});
            },

            update() {
                let uri = '/threads/' + this.thread.channel.slug + '/' + this.thread.slug;
                
                axios.patch(uri, this.form).then(() => {
					this.title = this.form.title;
					this.body = this.form.body;

					this.editing = false;

                    flash('Your thread has been updated.');
				}).catch(function (error) {
					if (error.response.status == 403) {
						flash('Your action is unauthorized.', 'danger');
						return;
					}

					flash('An error has occurred.', 'danger');
				});
            },

            resetForm() {
                this.form = {
                    title: this.title,
                    body: this.body,
                };

                this.editing = false;
            },
        },
    }
</script>