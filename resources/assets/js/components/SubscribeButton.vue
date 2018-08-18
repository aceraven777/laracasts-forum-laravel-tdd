<template>
    <button type='submit' :class="classes" v-if="true" @click="subscribe">Subscribe</button>
    <button type='submit' class='btn btn-danger' v-else>Unsubscribe</button>
</template>

<script>
    export default {
        props: ['active'],

        computed: {
            classes() {
                return [
                    'btn',
                    this.active ? 'btn-primary' : 'btn-default'
                ];
            },
        },

        methods: {
            subscribe() {
                let requestType = this.active ? 'delete' : 'post';

                axios[requestType](location.pathname + '/subscriptions').then(() => {
                    this.active = ! this.active;
                });
            },
        },
    }
</script>