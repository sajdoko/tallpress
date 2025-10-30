<div>
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Posts</div>
            <div class="stat-value">{{ $stats['total_posts'] }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Published</div>
            <div class="stat-value">{{ $stats['published_posts'] }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Drafts</div>
            <div class="stat-value">{{ $stats['draft_posts'] }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Pending</div>
            <div class="stat-value">{{ $stats['pending_posts'] }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Categories</div>
            <div class="stat-value">{{ $stats['total_categories'] }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Tags</div>
            <div class="stat-value">{{ $stats['total_tags'] }}</div>
        </div>

        @if(tallpress_setting('comments_enabled', true))
        <div class="stat-card">
            <div class="stat-label">Comments</div>
            <div class="stat-value">{{ $stats['total_comments'] }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Pending Comments</div>
            <div class="stat-value">{{ $stats['pending_comments'] }}</div>
        </div>
        @endif

        @can('manageUsers', \Sajdoko\TallPress\Models\Post::class)
        <div class="stat-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-value">{{ $stats['total_users'] }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Admins</div>
            <div class="stat-value">{{ $stats['admin_users'] }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Editors</div>
            <div class="stat-value">{{ $stats['editor_users'] }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Authors</div>
            <div class="stat-value">{{ $stats['author_users'] }}</div>
        </div>
        @endcan
    </div>

    <!-- Recent Activity -->
    <div class="content-grid">
        <!-- Recent Posts -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Posts</h2>
                <a href="{{ route('tallpress.admin.posts.index') }}" class="btn-link">View All</a>
            </div>
            <div class="card-body">
                @if($recentPosts->count() > 0)
                    <div class="list">
                        @foreach($recentPosts as $post)
                            <div class="list-item">
                                <div>
                                    <a href="{{ route('tallpress.admin.posts.edit', $post) }}" class="font-medium">
                                        {{ $post->title }}
                                    </a>
                                    <p class="text-sm text-gray-500">
                                        by {{ $post->author->name }} &middot; {{ $post->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <span class="badge badge-{{ $post->status }}">{{ ucfirst($post->status) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No posts yet.</p>
                @endif
            </div>
        </div>

        <!-- Recent Comments -->
        @if(tallpress_setting('comments_enabled', true))
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Comments</h2>
                <a href="{{ route('tallpress.admin.comments.index') }}" class="btn-link">View All</a>
            </div>
            <div class="card-body">
                @if($recentComments->count() > 0)
                    <div class="list">
                        @foreach($recentComments as $comment)
                            <div class="list-item">
                                <div>
                                    <p class="text-sm">{{ Str::limit($comment->body, 60) }}</p>
                                    <p class="text-xs text-gray-500">
                                        @if($comment->post)
                                            on <a href="{{ route('tallpress.posts.show', $comment->post->slug) }}" target="_blank">{{ $comment->post->title }}</a>
                                            &middot;
                                        @else
                                            on [deleted post] &middot;
                                        @endif
                                        {{ $comment->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                @if(!$comment->approved)
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-success">Approved</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No comments yet.</p>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
