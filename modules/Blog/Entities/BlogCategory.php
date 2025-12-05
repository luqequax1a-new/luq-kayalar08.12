<?php

namespace Modules\Blog\Entities;

use Carbon\Carbon;
use Spatie\Sitemap\Tags\Url;
use Modules\Support\Eloquent\Model;
use Modules\Meta\Eloquent\HasMetaData;
use Modules\Support\Eloquent\Sluggable;
use Modules\Blog\Admin\BlogCategoryTable;
use Spatie\Sitemap\Contracts\Sitemapable;
use Modules\Support\Eloquent\Translatable;

class BlogCategory extends Model implements Sitemapable
{
    use Translatable, Sluggable, HasMetaData;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatedAttributes = ['name'];

    /**
     * The attribute that will be slugged.
     *
     * @var string
     */
    protected $slugAttribute = 'name';


    public function table()
    {
        return new BlogCategoryTable($this->query());
    }


    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }


    public function url()
    {
        return route('blog_category.blog_posts.index', ['category' => $this->slug]);
    }


    public function toSitemapTag(): Url|string|array
    {
        $changefreq = setting('support.sitemap.blog_categories_changefreq', Url::CHANGE_FREQUENCY_WEEKLY);
        $priority = (float) setting('support.sitemap.blog_categories_priority', 0.5);

        return Url::create($this->url())
            ->setLastModificationDate(Carbon::create($this->updated_at))
            ->setChangeFrequency($changefreq)
            ->setPriority($priority);
    }
}
