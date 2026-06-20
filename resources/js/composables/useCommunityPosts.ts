import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { CommunityPost } from '@/types/community';

export function useCommunityPosts() {
    const deletePostForm = useForm({});
    const editPostForm = useForm({
        title: '',
        content: '',
    });
    const editingPost = ref<CommunityPost | null>(null);

    const openPostEditor = (post: CommunityPost) => {
        if (!post.is_own_post) {
            return;
        }

        editingPost.value = post;
        editPostForm.defaults({
            title: post.title ?? '',
            content: post.content ?? '',
        });
        editPostForm.reset();
        editPostForm.clearErrors();
    };

    const closePostEditor = () => {
        editingPost.value = null;
        editPostForm.reset();
        editPostForm.clearErrors();
    };

    const updateCommunityPost = () => {
        if (!editingPost.value) {
            return;
        }

        editPostForm.patch(`/community/posts/${editingPost.value.id}`, {
            preserveScroll: true,
            onSuccess: closePostEditor,
        });
    };

    const deleteCommunityPost = (post: CommunityPost) => {
        if (
            !window.confirm(
                'Supprimer cette publication du feed communautaire ?',
            )
        ) {
            return;
        }

        deletePostForm.delete(`/community/posts/${post.id}`, {
            preserveScroll: true,
        });
    };

    return {
        deletePostForm,
        editPostForm,
        editingPost,
        openPostEditor,
        closePostEditor,
        updateCommunityPost,
        deleteCommunityPost,
    };
}
