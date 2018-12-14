<template>
    <div>
        <input id="trix" type="hidden" :name="name" :value="value" ref="aaa">

        <trix-editor ref="trix" input="trix" :placeholder="placeholder"></trix-editor>
    </div>
</template>

<script>
    import Trix from 'trix';

    export default {
        props: ['name', 'value', 'placeholder', 'shouldClear'],

        mounted () {
            this.$refs.trix.addEventListener('trix-initialize', (e) => {
                this.$emit('trix-initialize', e);
            });

            this.$refs.trix.addEventListener('trix-change', (e) => {
                this.$emit('input', e.target.innerHTML);
            });

            this.$refs.trix.addEventListener('keydown', (e) => {
                this.$emit('keydown', e);
            });

            this.$watch('shouldClear', () => {
                if (this.shouldClear) {
                    this.$refs.trix.value = '';
                }
            });
        },
    }
</script>

<style scoped>
    trix-editor {
        min-height: 100px;
    }
</style>