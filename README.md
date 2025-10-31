# Laravel TallPress Blog Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sajdoko/tallpress.svg?style=flat-square)](https://packagist.org/packages/sajdoko/tallpress)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/sajdoko/tallpress/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/sajdoko/tallpress/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/sajdoko/tallpress.svg?style=flat-square)](https://packagist.org/packages/sajdoko/tallpress)

A comprehensive, reusable Laravel blog package that adds complete blog functionality to any Laravel 10+ application. Features include posts with Markdown support, categories, tags, comments, image uploads, search, policies, and a full REST API.

## Documentation

Complete documentation is available in the [**Wiki**](https://github.com/sajdoko/tallpress/wiki):

- **[Installation Guide](https://github.com/sajdoko/tallpress/wiki/Installation)** - Requirements, installation steps, and quick setup
- **[Configuration](https://github.com/sajdoko/tallpress/wiki/Configuration)** - Configuration options and dynamic settings management
- **[Usage Guide](https://github.com/sajdoko/tallpress/wiki/Usage-Guide)** - Routes, creating posts, events, and Artisan commands
- **[Admin Interface](https://github.com/sajdoko/tallpress/wiki/Admin-Interface)** - Complete admin panel guide with authorization and features
- **[API Documentation](https://github.com/sajdoko/tallpress/wiki/API-Documentation)** - RESTful API endpoints and authentication
- **[Styling & Assets](https://github.com/sajdoko/tallpress/wiki/Styling-and-Assets)** - Customizing styles and working with assets
- **[Contributing](https://github.com/sajdoko/tallpress/wiki/Contributing)** - Development workflow and testing

## Features

### Content Management

- **Posts Management**: Create, read, update, and delete blog posts with rich editing capabilities
- **Rich WYSIWYG Editor**: Modern Quill editor with inline image uploads, formatting toolbar, and drag & drop support
- **Markdown Support**: Write posts in Markdown with automatic HTML conversion using league/commonmark (backward compatible)
- **Categories & Tags**: Many-to-many relationships for organizing content with auto-slug generation
- **Comments System**: User comments with approval/moderation workflow
- **Featured Images**: Upload and manage featured images with drag & drop support, or use external image URLs
- **Image Management**: Professional hierarchical image organization by year/month with metadata tracking
- **Post Status Workflow**: Draft → Pending → Published workflow with role-based publishing
- **Soft Deletes**: Posts are soft-deleted for recovery
- **Post Revisions**: Automatic version tracking with restore capability (configurable retention)
- **Metadata**: JSON metadata field for custom data storage

### Admin Interface

- **Full Admin Dashboard**: Comprehensive admin panel at `/admin/blog` with statistics and overview
- **Role-Based Access Control**: Three roles (admin, editor, author) with granular permissions
- **Advanced Filtering**: Filter posts by status, author, category, date range, and search
- **Bulk Actions**: Publish, unpublish, and delete multiple posts at once
- **CSV Export**: Export posts to CSV for analysis or backup
- **Media Manager**: Upload, browse, and manage media files with thumbnails and metadata (dimensions, file size)
- **Inline Image Uploads**: Drag & drop or paste images directly into the editor for seamless content creation
- **Comments Moderation**: Approve, reject, and bulk manage comments
- **Activity Logging**: Track all admin actions with user attribution
- **Rich Editor**: Choice between Quill WYSIWYG editor or Markdown textarea with live preview
- **Responsive Design**: Mobile-friendly admin interface

### Search & Discovery

- **Full-Text Search**: Search across post title, excerpt, and body content
- **Configurable Search**: Define which fields are searchable
- **Category Browsing**: View posts by category with post counts
- **Tag Filtering**: Filter posts by tags
- **Published Posts Scope**: Easy querying of public content

### Authorization & Security

- **Post Policies**: Granular permissions (viewAny, view, create, update, delete, publish)
- **Role-Based Authorization**: Admin, editor, and author roles with different capabilities
- **Gate Integration**: Custom gates for admin access and content moderation
- **Middleware Protection**: Route protection with `EnsureTallPressRole` middleware
- **Comment Moderation**: Approval workflow for user comments

### API & Integration

- **REST API**: Complete JSON API for all CRUD operations
- **API Resources**: Consistent JSON responses with proper formatting
- **Sanctum Support**: Token-based authentication for API access
- **HTTP Standards**: Proper status codes and validation responses
- **Extensible**: Easy to extend with custom API endpoints

### Developer Experience

- **Precompiled Assets**: Zero-build installation - works immediately after `composer require`
- **Events & Observers**: PostPublished event and PostObserver for extensibility
- **Artisan Commands**: Install, seed, and cleanup commands for easy setup
- **Localization**: Translation-ready strings in multiple languages
- **Seeders**: Generate demo data for testing and development
- **Factory Classes**: Test data generation for all models
- **Comprehensive Tests**: Unit and feature tests included
- **PSR-12 Standards**: Clean, maintainable code following Laravel conventions

## Quick Start

### Installation

```bash
composer require sajdoko/tallpress
php artisan vendor:publish --provider="Sajdoko\TallPress\TallPressServiceProvider" --tag=tallpress-config
php artisan migrate
```

Or use the quick install command:

```bash
php artisan tallpress:install --seed
```

That's it! Visit `/blog` for the public blog and `/admin/blog` for the admin interface.

### Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- MySQL database
- Livewire 3.0 or higher (automatically installed)

## Key Features Highlight

### Zero-Build Installation

The package ships with **precompiled assets** - no npm or build step required. Works immediately after `composer require`.

### Livewire-Powered Admin

Built with **Livewire v3** for a modern, reactive admin experience:

- Real-time updates without page reloads
- Live search and filtering
- Inline editing
- Bulk actions
- Live file upload feedback

### Flexible Editor

Choose your preferred editing experience:

- **Rich Text Editor**: Modern WYSIWYG with Quill (drag & drop images, formatting toolbar)
- **Markdown Editor**: Classic Markdown with live preview

### Complete API

RESTful JSON API with Sanctum authentication:

```bash
GET    /api/tallpress/posts          # List posts
GET    /api/tallpress/posts/{id}     # Show post
POST   /api/tallpress/posts          # Create post (auth required)
PUT    /api/tallpress/posts/{id}     # Update post (auth required)
DELETE /api/tallpress/posts/{id}     # Delete post (auth required)
```

## Usage Example

```php
// Display recent posts in your app
$recentPosts = \Sajdoko\TallPress\Models\Post::published()
    ->latest('published_at')
    ->limit(5)
    ->get();

// Create a post programmatically
$post = Post::create([
    'title' => 'My First Blog Post',
    'excerpt' => 'A brief introduction',
    'body' => '## Hello World

This is **Markdown** content.',
    'status' => 'published',
    'author_id' => auth()->id(),
]);
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Maintainers

For package maintainers who need to compile and update assets, see [MAINTAINERS.md](MAINTAINERS.md).
