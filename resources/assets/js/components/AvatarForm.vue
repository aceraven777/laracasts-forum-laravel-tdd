<template>
    <div>
        <div class="level">
            <img :src="avatar" :alt="user.name" class="mr-1" width="200" height="200" />        

            <h1 v-text="user.name"></h1>
        </div>

        <div>
            <form v-if="canUpdate" method="POST" enctype="multipart/form-data">
                <image-upload name="avatar" @loaded="onLoaded"></image-upload>
            </form>
        </div>
    </div>
</template>

<script>
    import ImageUpload from './ImageUpload.vue';

    export default {
        props: ['user', 'canUpdate'],

        components: { ImageUpload },

        data() {
            return {
                avatar: this.user.avatar_path,
                file: ''
            };
        },

        methods: {
            onLoaded(avatar) {
                this.avatar = avatar.src;

                this.persist(avatar.file);
            },

            persist(avatar) {
                let data = new FormData();

                data.append('avatar', avatar);

                axios.post('/api/users/' + this.user.name + '/avatar',
                    data,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                )
				.then(({data}) => {
					flash('Avatar uploaded!');
                    this.$emit('change', avatar);
				});
            },
        },
    }
</script>