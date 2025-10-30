# Maintainer Development Guide

This guide is for package maintainers who need to compile and update the CSS/JS assets for the `sajdoko/tallpress` package.

> **Note:** For general package documentation, see the [Wiki](https://github.com/sajdoko/tallpress/wiki).

## Prerequisites

- Node.js 18+ and npm
- Composer
- PHP 8.1+

## Initial Setup

1. **Install Node Dependencies**

   ```bash
   npm install
   ```

2. **Install PHP Dependencies**

   ```bash
   composer install
   ```

## Development Workflow

### Compiling Assets

The package uses Vite + Tailwind CSS to compile assets into the `public/` directory, which is what gets published to end-user applications.

**Development Mode (with watch):**

```bash
npm run dev
```

This watches for file changes and recompiles automatically.

**Production Build:**

```bash
npm run build
```

This creates optimized production-ready assets in `public/css/` and `public/js/`.

**Watch Mode (for incremental builds):**

```bash
npm run watch
```

### Source Files

The package now has separate assets for frontend and admin:

**Frontend Assets (for public blog pages):**

- **CSS Source**: `resources/css/tallpress-frontend.css` - Tailwind directives and styles (no Quill CSS)
- **JS Source**: `resources/js/tallpress-frontend.js` - Minimal JavaScript (no Quill editor)
- **Compiled Output**:
  - `public/css/tallpress-frontend.css` - Production-ready CSS (~40KB)
  - `public/js/tallpress-frontend.js` - Production-ready JavaScript (~44 bytes)

**Admin Assets (for admin panel):**

- **CSS Source**: `resources/css/tallpress-admin.css` - Tailwind directives, styles, and Quill CSS
- **JS Source**: `resources/js/tallpress-admin.js` - JavaScript with Quill editor integration
- **Compiled Output**:
  - `public/css/tallpress-admin.css` - Production-ready CSS (~70KB)
  - `public/js/tallpress-admin.js` - Production-ready JavaScript (~200KB)

### Tailwind Configuration

The Tailwind config (`tailwind.config.js`) scans these paths for class usage:

- `./resources/views/**/*.blade.php` - All Blade templates
- `./src/Livewire/**/*.php` - All Livewire components

### Adding New Styles

1. **Using Tailwind Utility Classes**
   - Add classes directly in Blade templates or Livewire components
   - Tailwind will auto-generate the CSS during build

2. **Custom Component Classes**
   - Add to the appropriate CSS file:
     - `resources/css/tallpress-frontend.css` for public blog styles
     - `resources/css/tallpress-admin.css` for admin-only styles
   - Follow the existing pattern in `@layer components`

Example:

```css
@layer components {
    .my-custom-component {
        @apply bg-blue-500 text-white px-4 py-2 rounded-md;
    }
}
```

3. **Rebuild Assets**

   ```bash
   npm run build
   ```

## Testing Changes

### In Package Development

1. Make changes to CSS/JS source files
2. Run `npm run build`
3. The compiled files in `public/` are ready

### In a Host Application

1. Build the assets: `npm run build`
2. In your test Laravel app, run:

   ```bash
   composer update sajdoko/tallpress
   php artisan vendor:publish --tag=tallpress-assets --force
   php artisan view:clear
   ```

3. Refresh your browser

### Using Path Repository (Recommended for Development)

In your test Laravel app's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../tallpress",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "sajdoko/tallpress": "*"
    }
}
```

Then:

```bash
composer update sajdoko/tallpress
```

This creates a symlink, so changes to `public/` are immediately available.

## Release Workflow

### Before Releasing a New Version

1. **Update all assets:**

   ```bash
   npm run build
   ```

2. **Commit the compiled assets:**

   ```bash
   git add public/css/tallpress-frontend.css public/css/tallpress-admin.css public/js/tallpress-frontend.js public/js/tallpress-admin.js
   git commit -m "build: compile assets for vX.X.X"
   ```

3. **Run tests:**

   ```bash
   composer test
   composer analyse
   composer format
   ```

4. **Update CHANGELOG.md** with asset changes if applicable

5. **Tag the release:**

   ```bash
   git tag v0.7.0
   git push origin v0.7.0
   git push
   ```

## Important Notes

### Why Compile to `public/`?

The compiled assets in `public/` are what get published to end-user applications via:

```bash
php artisan vendor:publish --tag=tallpress-assets
```

This copies `vendor/sajdoko/tallpress/public/*` to `public/vendor/tallpress/` in the host app.

### End Users Don't Need Node.js

End users of the package:

- ✅ Get precompiled CSS/JS from `public/`
- ✅ No npm install needed
- ✅ No build step required
- ✅ Works immediately after `composer require`

### Maintainers DO Need Node.js

Maintainers (you) need Node.js to:

- Compile Tailwind CSS with only the classes actually used
- Update styles when adding new components
- Optimize and minify assets for production

## File Structure

```
sajdoko/tallpress/
├── resources/
│   ├── css/
│   │   ├── tallpress-frontend.css  # Source CSS for public pages (no Quill)
│   │   ├── tallpress-admin.css     # Source CSS for admin (with Quill)
│   ├── js/
│   │   ├── tallpress-frontend.js   # Source JS for public pages
│   │   ├── tallpress-admin.js      # Source JS for admin (with Quill)
│   │   ├── quill-editor.js    # Quill editor configuration
│   └── views/                # Blade templates (scanned by Tailwind)
│       ├── admin/
│       └── livewire/
├── src/
│   └── Livewire/             # Livewire components (scanned by Tailwind)
├── public/                   # COMPILED OUTPUT (committed to git)
│   ├── css/
│   │   ├── tallpress-frontend.css # ← Published to end users (~40KB)
│   │   └── tallpress-admin.css    # ← Published to end users (~70KB)
│   └── js/
│       ├── tallpress-frontend.js  # ← Published to end users (~44 bytes)
│       └── tallpress-admin.js     # ← Published to end users (~200KB)
├── tailwind.config.js        # Tailwind configuration
├── vite.config.js            # Vite build configuration
├── postcss.config.js         # PostCSS configuration
└── package.json              # Node dependencies
```

## Troubleshooting

### Assets Not Updating in Host App

```bash
# In host Laravel app
php artisan vendor:publish --tag=tallpress-assets --force
php artisan view:clear
php artisan config:clear
```

### Missing Tailwind Classes

1. Check that the class is used in a file scanned by Tailwind (see `tailwind.config.js`)
2. Rebuild: `npm run build`
3. Check `public/css/tallpress-frontend.css` or `public/css/tallpress-admin.css` for the generated class

### Build Errors

```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

## Questions?

For questions about asset compilation or development setup, open an issue on GitHub with the `maintainer` label.
