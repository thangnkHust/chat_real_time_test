<?php

namespace App\Transformers;
use App\Model\Post;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract {

	protected $defaultIncludes  = [
		'creator'
	];

    public function transform(Post $post) {
        return [
            'content' => $post->content,
            'id' => $post->id,
            'is_parent' => $post->is_parent,
            'channel_id' => $post->channel_id,
            'parent_id' => $post->parent_id,
            'creator' => $post->creator,
            'user_following_post' => $post->user_following_post,
            'status' => $post->status,
            'type' => $post->type,
	        'created_at' => $post->created_at,
	        'updated_at' => $post->updated_at,
	        'number_children_posts' => $post->children()->count()
        ];
    }

	public function includeCreator(Post $post)
	{
		if ($user = $post->creator()->first()) {
			return $this->item($user, new UserTransformer());
		}
	}
}
