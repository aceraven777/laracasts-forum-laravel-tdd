<template>
    <div class="new-reply">
        <div v-if="signedIn">
            <div class="form-group">
                <wysiwyg v-model="body" name="body" placeholder="Have something to say?" :shouldClear="completed" ref="trix"></wysiwyg>
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
    import Tribute from "tributejs";

    export default {
        data() {
            return {
                body: '',
                completed: false,
            };
        },

        mounted() {
            let component = this;
            let tribute;

            this.$refs.trix.$on('trix-initialize', (e) => {
                tribute = new Tribute({
                    menuItemTemplate: function (item) {
                        return item.string;
                    },
                    lookup: 'name',
                    fillAttr: 'name',
                    values: function (query, callback) {
                        if (! query) {
                            return;
                        }

                        $.getJSON("/api/users", {name: query}, function(users) {
                            callback(users);
                        });
                    },
                    selectTemplate: function (item) {
                        return '<span contenteditable="false"><a href="/profiles/' + item.original.name + '" >@' + item.original.name + '</a></span>';
                    }
                });

                this.$refs.trix.$on('keydown', (e) => {
                    // If user has pressed space
                    if (e.keyCode == 32) {
                        tribute.hideMenu();
                    }
                });

                tribute.attach(this.$refs.trix.$refs.trix);
            });
        },

        methods: {
            addReply() {
                this.completed = false;

                axios.post(location.pathname + '/replies', {
                    body: this.body,
                })
                .catch((error) => {
                    flash(error.response.data, 'danger');
                })
                .then(({data}) => {
                    this.body = '';
                    this.completed = true;

                    flash('Your reply has been posted.');

                    this.$emit('created', data);
                });
            },
        },
    }
</script>

<style scoped>
    .new-reply {
        padding: 15px;
        background-color: #fff;
        border: 1px solid #e3e3e3;
    }
</style>