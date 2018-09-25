<template>
    <div>
        <div v-if="signedIn">
            <div class="form-group">
                <textarea name="body"
                    id="body"
                    class="form-control"
                    placeholder="Have something to say?"
                    rows="5"
                    required
                    v-model="body"></textarea>
            </div>

            <button type="submit"
                class="btn btn-primary"
                @click="addReply">Post</button>
        </div>
        <p v-else class="text-center">Please <a href="/login">sign in</a> to participate in this discussion.</p>
    </div>
</template>

<script>
    import 'jquery.caret';
    import 'at.js';

    export default {
        data() {
            return {
                body: '',
            };
        },

        computed: {
            signedIn() {
                return window.App.signedIn;
            },
        },

        mounted() {
            var component = this;

            $('#body').atwho({
                at: "@",
                delay: 750,
                // data: ['yeye', 'bonel'],
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
                component.body = $(this).val();
            });
        },

        methods: {
            addReply() {
                axios.post(location.pathname + '/replies', {
                    body: this.body,
                })
                .catch((error) => {
                    flash(error.response.data, 'danger');
                })
                .then(({data}) => {
                    this.body = '';

                    flash('Your reply has been posted.');

                    this.$emit('created', data);
                });
            },
        },
    }
</script>