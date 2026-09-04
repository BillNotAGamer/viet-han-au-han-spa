<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\MediaTranslation;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostCategoryTranslation;
use App\Models\PostTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicBlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_routes_render_and_vi_prefixed_route_is_not_canonical(): void
    {
        $this->get('/blog')->assertStatus(200);
        $this->get('/en/blog')->assertStatus(200);
        $this->get('/vi')->assertStatus(301)->assertRedirect('/');
        $this->get('/vi/blog')->assertStatus(404);
    }

    public function test_empty_database_blog_listing_renders_gracefully(): void
    {
        $this->get('/blog')
            ->assertStatus(200)
            ->assertSee(__('blog.index.empty', [], 'vi'))
            ->assertDontSee('Post #');

        $this->get('/en/blog')
            ->assertStatus(200)
            ->assertSee(__('blog.index.empty', [], 'en'))
            ->assertDontSee('Post #');
    }

    public function test_listing_filters_by_publication_status_and_published_at_schedule(): void
    {
        $published = $this->createPostWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Visible Published Article',
            'visible-published-article',
            ['published_at' => now()->subMinute()]
        );
        $draft = $this->createPostWithTranslation(
            ContentStatus::DRAFT,
            'vi',
            'Hidden Draft Article',
            'hidden-draft-article',
            ['published_at' => null]
        );
        $archived = $this->createPostWithTranslation(
            ContentStatus::ARCHIVED,
            'vi',
            'Hidden Archived Article',
            'hidden-archived-article',
            ['published_at' => now()->subMinute()]
        );
        $future = $this->createPostWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Hidden Future Article',
            'hidden-future-article',
            ['published_at' => now()->addDay()]
        );

        $response = $this->get('/blog');

        $response->assertStatus(200);
        $response->assertSee('Visible Published Article');
        $response->assertDontSee('Hidden Draft Article');
        $response->assertDontSee('Hidden Archived Article');
        $response->assertDontSee('Hidden Future Article');

        $this->get('/blog/'.$published->translationFor('vi')->slug)->assertStatus(200);
        $this->get('/blog/'.$draft->translationFor('vi')->slug)->assertStatus(404);
        $this->get('/blog/'.$archived->translationFor('vi')->slug)->assertStatus(404);
        $this->get('/blog/'.$future->translationFor('vi')->slug)->assertStatus(404);
    }

    public function test_public_blog_requires_exact_requested_locale_without_vi_fallback(): void
    {
        $post = $this->createPostWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'VI Only Article',
            'vi-only-article'
        );

        $this->get('/blog')
            ->assertStatus(200)
            ->assertSee('VI Only Article');

        $this->get('/en/blog')
            ->assertStatus(200)
            ->assertDontSee('VI Only Article');

        $this->get('/blog/vi-only-article')->assertStatus(200);
        $this->get('/en/blog/vi-only-article')->assertStatus(404);

        PostTranslation::create([
            'post_id' => $post->id,
            'locale' => 'en',
            'title' => 'English Article',
            'slug' => 'english-article',
            'excerpt' => 'English article excerpt',
            'content' => '<p>English article body.</p>',
        ]);

        $this->get('/en/blog')
            ->assertStatus(200)
            ->assertSee('English Article')
            ->assertDontSee('VI Only Article');

        $this->get('/en/blog/english-article')->assertStatus(200);
    }

    public function test_blog_detail_slug_is_locale_specific(): void
    {
        $post = $this->createPostWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Cham Soc Da Dung Cach',
            'cham-soc-da-dung-cach'
        );

        PostTranslation::create([
            'post_id' => $post->id,
            'locale' => 'en',
            'title' => 'Proper Skin Care',
            'slug' => 'proper-skin-care',
            'excerpt' => 'Proper skin care excerpt',
            'content' => '<p>Proper skin care body.</p>',
        ]);

        $this->get('/blog/cham-soc-da-dung-cach')->assertStatus(200);
        $this->get('/en/blog/proper-skin-care')->assertStatus(200);
        $this->get('/blog/proper-skin-care')->assertStatus(404);
        $this->get('/en/blog/cham-soc-da-dung-cach')->assertStatus(404);
    }

    public function test_category_label_uses_exact_locale_only_without_category_archive_routes(): void
    {
        $category = PostCategory::create([
            'status' => ContentStatus::PUBLISHED,
            'sort_order' => 1,
        ]);
        PostCategoryTranslation::create([
            'post_category_id' => $category->id,
            'locale' => 'vi',
            'name' => 'Danh Muc Blog Tieng Viet',
            'slug' => 'danh-muc-blog-tieng-viet',
        ]);

        $this->createPostWithTranslation(
            ContentStatus::PUBLISHED,
            'en',
            'English Article Without English Category',
            'english-article-without-english-category',
            ['post_category_id' => $category->id]
        );

        $this->get('/en/blog')
            ->assertStatus(200)
            ->assertSee('English Article Without English Category')
            ->assertDontSee('Danh Muc Blog Tieng Viet');

        $this->get('/blog/category/danh-muc-blog-tieng-viet')->assertStatus(404);
        $this->get('/en/blog/category/danh-muc-blog-tieng-viet')->assertStatus(404);
    }

    public function test_author_public_output_uses_safe_name_without_account_sensitive_fields(): void
    {
        $author = User::create([
            'name' => 'Safe Author Name',
            'email' => 'author@example.test',
            'password' => 'hashed-secret',
        ]);
        $author->forceFill(['is_admin' => true])->save();

        $this->createPostWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Article With Author',
            'article-with-author',
            ['author_id' => $author->id]
        );

        $this->get('/blog/article-with-author')
            ->assertStatus(200)
            ->assertSee('Safe Author Name')
            ->assertDontSee('author@example.test')
            ->assertDontSee('is_admin')
            ->assertDontSee('hashed-secret')
            ->assertDontSee('remember_token');
    }

    public function test_rich_editor_content_is_rendered_as_safe_plain_text_without_raw_markup(): void
    {
        $post = $this->createPostWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Rich Content Safety Article',
            'rich-content-safety-article'
        );
        $post->translationFor('vi')->update([
            'content' => '<p>Visible body paragraph.</p><script>alert("x")</script><strong>Styled text</strong><img src=x onerror=alert(1)>',
            'seo_title' => 'SEO Title Must Not Render',
            'seo_description' => 'SEO Description Must Not Render',
        ]);

        $this->get('/blog/rich-content-safety-article')
            ->assertStatus(200)
            ->assertSee('Visible body paragraph.')
            ->assertSee('Styled text')
            ->assertDontSee('<script>alert', false)
            ->assertDontSee('alert("x")')
            ->assertDontSee('<strong>', false)
            ->assertDontSee('onerror', false)
            ->assertDontSee('SEO Title Must Not Render')
            ->assertDontSee('SEO Description Must Not Render');
    }

    public function test_existing_missing_and_gallery_blog_media_are_resolved_without_crashing(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/blog-hero.jpg', 'fake-image');
        Storage::disk('public')->put('media/blog-gallery-one.jpg', 'fake-image');
        Storage::disk('public')->put('media/blog-gallery-two.jpg', 'fake-image');

        $hero = $this->createMedia('media/blog-hero.jpg');
        MediaTranslation::create([
            'media_id' => $hero->id,
            'locale' => 'vi',
            'alt_text' => 'Exact VI Blog Hero Alt',
        ]);

        $galleryOne = $this->createMedia('media/blog-gallery-one.jpg');
        MediaTranslation::create([
            'media_id' => $galleryOne->id,
            'locale' => 'vi',
            'alt_text' => 'Exact VI Blog Gallery One Alt',
            'caption' => 'First gallery caption',
        ]);

        $galleryTwo = $this->createMedia('media/blog-gallery-two.jpg');
        MediaTranslation::create([
            'media_id' => $galleryTwo->id,
            'locale' => 'vi',
            'alt_text' => 'Exact VI Blog Gallery Two Alt',
        ]);

        $post = $this->createPostWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Blog With Real Media',
            'blog-with-real-media',
            ['hero_media_id' => $hero->id]
        );
        $post->media()->attach($galleryTwo->id, ['sort_order' => 1, 'created_at' => now()]);
        $post->media()->attach($galleryOne->id, ['sort_order' => 0, 'created_at' => now()]);

        $this->get('/blog')
            ->assertStatus(200)
            ->assertSee('blog-hero.jpg')
            ->assertSee('Exact VI Blog Hero Alt');

        $this->get('/blog/blog-with-real-media')
            ->assertStatus(200)
            ->assertSee('blog-hero.jpg')
            ->assertSee('Exact VI Blog Hero Alt')
            ->assertSeeInOrder(['blog-gallery-one.jpg', 'blog-gallery-two.jpg'])
            ->assertSee('Exact VI Blog Gallery One Alt')
            ->assertSee('Exact VI Blog Gallery Two Alt')
            ->assertSee('First gallery caption');

        $missingMedia = $this->createMedia('media/missing-blog.jpg');
        $missingPost = $this->createPostWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Blog With Missing Media',
            'blog-with-missing-media',
            ['hero_media_id' => $missingMedia->id]
        );

        $this->get('/blog/'.$missingPost->translationFor('vi')->slug)
            ->assertStatus(200)
            ->assertSee('Blog With Missing Media')
            ->assertDontSee('missing-blog.jpg');
    }

    public function test_wrong_locale_blog_media_alt_text_does_not_leak(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/en-blog-alt.jpg', 'fake-image');

        $media = $this->createMedia('media/en-blog-alt.jpg');
        MediaTranslation::create([
            'media_id' => $media->id,
            'locale' => 'en',
            'alt_text' => 'English Blog Alt Must Not Leak',
        ]);

        $this->createPostWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Blog With Locale Safe Alt',
            'blog-with-locale-safe-alt',
            ['hero_media_id' => $media->id]
        );

        $this->get('/blog/blog-with-locale-safe-alt')
            ->assertStatus(200)
            ->assertSee('en-blog-alt.jpg')
            ->assertSee('alt=""', false)
            ->assertDontSee('English Blog Alt Must Not Leak');
    }

    public function test_blog_detail_language_switch_targets_exact_translated_slug_and_index_fallback(): void
    {
        $post = $this->createPostWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Cham Soc Chuyen Sau',
            'cham-soc-chuyen-sau'
        );
        PostTranslation::create([
            'post_id' => $post->id,
            'locale' => 'en',
            'title' => 'Advanced Care',
            'slug' => 'advanced-care',
            'excerpt' => 'Advanced care excerpt',
            'content' => '<p>Advanced care body.</p>',
        ]);

        $this->get('/blog/cham-soc-chuyen-sau')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.blog.show', ['slug' => 'advanced-care']).'"', false);

        $this->get('/en/blog/advanced-care')
            ->assertStatus(200)
            ->assertSee('href="'.route('vi.blog.show', ['slug' => 'cham-soc-chuyen-sau']).'"', false);

        $this->createPostWithTranslation(
            ContentStatus::PUBLISHED,
            'vi',
            'Missing English Blog Article',
            'missing-english-blog-article'
        );

        $this->get('/blog/missing-english-blog-article')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.blog.index').'"', false);
    }

    public function test_header_blog_navigation_targets_real_listing_routes_without_visual_class_changes(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('href="'.route('vi.blog.index').'"', false)
            ->assertSee('href="'.route('vi.services.index').'"', false)
            ->assertSee('href="'.route('vi.training.index').'"', false)
            ->assertSee('class="public-header__link"', false);

        $this->get('/en')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.blog.index').'"', false)
            ->assertSee('href="'.route('en.services.index').'"', false)
            ->assertSee('href="'.route('en.training.index').'"', false)
            ->assertSee('class="public-header__link"', false);

        $this->get('/blog')
            ->assertStatus(200)
            ->assertSee('public-header--sticky', false)
            ->assertSee('href="'.route('vi.blog.index').'"', false);
    }

    public function test_phase_10c_adds_no_public_blog_mutation_routes(): void
    {
        $publicBlogMutationRoutes = collect(Route::getRoutes())
            ->filter(fn ($route) => str_contains($route->uri(), 'blog'))
            ->filter(fn ($route) => in_array('POST', $route->methods(), true))
            ->reject(fn ($route) => str_starts_with($route->uri(), 'admin'))
            ->values();

        $this->assertCount(0, $publicBlogMutationRoutes);
    }

    /**
     * @param  array<string, mixed>  $postOverrides
     */
    protected function createPostWithTranslation(
        ContentStatus $status,
        string $locale,
        string $title,
        string $slug,
        array $postOverrides = []
    ): Post {
        $categoryId = $postOverrides['post_category_id'] ?? PostCategory::create([
            'status' => ContentStatus::PUBLISHED,
            'sort_order' => 1,
        ])->id;

        $post = Post::create(array_merge([
            'post_category_id' => $categoryId,
            'author_id' => null,
            'hero_media_id' => null,
            'status' => $status,
            'is_featured' => false,
            'published_at' => $status === ContentStatus::PUBLISHED ? now()->subMinute() : null,
        ], $postOverrides));

        PostTranslation::create([
            'post_id' => $post->id,
            'locale' => $locale,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $title.' excerpt',
            'content' => '<p>'.$title.' content paragraph.</p>',
        ]);

        return $post->fresh(['translations']);
    }

    protected function createMedia(string $path): Media
    {
        return Media::create([
            'disk' => 'public',
            'path' => $path,
            'file_name' => basename($path),
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 1024,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
