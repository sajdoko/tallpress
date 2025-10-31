<?php

// config for Sajdoko/TallPress
return [
    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'per_page' => 15,

    /*
    |--------------------------------------------------------------------------
    | Author Model
    |--------------------------------------------------------------------------
    */
    'author_model' => 'App\\Models\\User',

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    */
    'storage_disk' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'route_prefix' => 'blog',
    'admin_route_prefix' => 'admin/blog',

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'web' => ['web'],
        'api' => ['api'],
        'admin' => ['web', 'auth'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Management
    |--------------------------------------------------------------------------
    | Configure how roles are managed in your application.
    |
    | The package can work with or without an external ACL system:
    |
    | 1. Built-in roles (default for fresh Laravel apps):
    |    - Set 'add_role_field' to true
    |    - Package adds a 'role' column to users table
    |    - Simple role-based authorization (admin, editor, author)
    |
    | 2. External ACL (Spatie Permission, Bouncer, Laratrust, etc.):
    |    - Set 'add_role_field' to false
    |    - Customize the 'access-tallpress-admin' gate in AppServiceProvider
    |    - Use your ACL package's methods in the gate definition
    |
    | The install command automatically detects external ACL systems and
    | configures this setting appropriately.
    */
    'roles' => [
        'add_role_field' => true,        // Add 'role' column to users table
        'role_field' => 'role',          // Field name for role
        'default_role' => 'author',      // Default role for new users
        'available_roles' => [
            'admin' => 'Administrator',
            'editor' => 'Editor',
            'author' => 'Author',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog Configuration
    |--------------------------------------------------------------------------
    */
    'blog' => [
        'title' => 'My Blog',
        'description' => 'Welcome to my blog!',
        'logo' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    */
    'search' => [
        'enabled' => true,
        'fields' => ['title', 'excerpt', 'body'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Comments Configuration
    |--------------------------------------------------------------------------
    */
    'comments' => [
        'enabled' => true,
        'require_approval' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Images Configuration
    |--------------------------------------------------------------------------
    */
    'images' => [
        'max_size' => 2048,  // KB
        'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'path' => 'images',  // Will be accessible at /images/
        'organize_by_date' => true,  // Organize images by year/month
        'use_seo_filenames' => true,  // Use SEO-friendly filenames instead of hashes
    ],

    /*
    |--------------------------------------------------------------------------
    | Editor Configuration
    |--------------------------------------------------------------------------
    */
    'editor' => [
        'type' => 'rich',  // 'rich' for WYSIWYG editor, 'markdown' for plain textarea
        'sanitize_html' => true,  // Sanitize HTML content for security
    ],

    /*
    |--------------------------------------------------------------------------
    | HTML Purifier Configuration
    |--------------------------------------------------------------------------
    */
    'html_purifier' => [
        'HTML.Allowed' => 'p,br,strong,em,u,s,a[href|title|target],ul,ol,li,blockquote,code,pre,h1,h2,h3,h4,h5,h6,img[src|alt|width|height],table,thead,tbody,tr,th,td',
        'HTML.AllowedAttributes' => 'href,title,target,src,alt,width,height',
        'HTML.TargetBlank' => true,
        'AutoFormat.RemoveEmpty' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Revisions Configuration
    |--------------------------------------------------------------------------
    */
    'revisions' => [
        'enabled' => true,
        'keep_revisions' => 10,  // Number of revisions to keep per post
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Logging
    |--------------------------------------------------------------------------
    */
    'activity_log' => [
        'enabled' => true,
        'keep_days' => 90,  // Days to keep activity logs
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Sharing
    |--------------------------------------------------------------------------
    */
    'social_share' => [
        'enabled' => false,
        'platforms' => [
            'facebook' => true,
            'twitter' => true,
            'linkedin' => true,
            'reddit' => false,
            'whatsapp' => false,
            'email' => true,
        ],
    ],
];
