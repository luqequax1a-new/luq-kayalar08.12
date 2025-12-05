<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Tag\Entities\Tag;
use Modules\Tag\Entities\TagBadge;

class TagBadgeController extends Controller
{
    public function index()
    {
        $badges = TagBadge::with('tag')
            ->orderByDesc('priority')
            ->orderBy('name')
            ->paginate(20);

        return view('admin::tag_badges.index', compact('badges'));
    }

    public function create()
    {
        $badge = new TagBadge();
        $tags  = Tag::all()->sortBy('name');

        return view('admin::tag_badges.form', compact('badge', 'tags'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('tag-badges', 'public');
        }

        $data['is_active']       = $request->has('is_active');
        $data['show_on_listing'] = $request->has('show_on_listing');
        $data['show_on_detail']  = $request->has('show_on_detail');

        TagBadge::create($data);

        return redirect()
            ->route('admin.tag_badges.index')
            ->with('success', 'Etiket görseli oluşturuldu.');
    }

    public function edit(TagBadge $tagBadge)
    {
        $badge = $tagBadge;
        $tags  = Tag::all()->sortBy('name');

        return view('admin::tag_badges.form', compact('badge', 'tags'));
    }

    public function update(Request $request, TagBadge $tagBadge)
    {
        $data = $this->validateData($request, $tagBadge->id);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('tag-badges', 'public');
        }

        $data['is_active']       = $request->has('is_active');
        $data['show_on_listing'] = $request->has('show_on_listing');
        $data['show_on_detail']  = $request->has('show_on_detail');

        $tagBadge->update($data);

        return redirect()
            ->route('admin.tag_badges.index')
            ->with('success', 'Etiket görseli güncellendi.');
    }

    public function destroy(TagBadge $tagBadge)
    {
        $tagBadge->delete();

        return redirect()
            ->route('admin.tag_badges.index')
            ->with('success', 'Etiket görseli silindi.');
    }

    protected function validateData(Request $request, $id = null): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255', 'unique:tag_badges,slug,' . $id],
            'tag_id'           => ['required', 'exists:tags,id'],
            'is_active'        => ['sometimes', 'boolean'],
            'show_on_listing'  => ['sometimes', 'boolean'],
            'listing_position' => ['required', 'in:top_left,top_right,bottom_left,bottom_right'],
            'show_on_detail'   => ['sometimes', 'boolean'],
            'detail_position'  => ['required', 'in:top_left,top_right,bottom_left,bottom_right'],
            'priority'         => ['nullable', 'integer'],
            'image'            => ['nullable', 'mimes:png,svg,webp,avif', 'max:512'],
        ]);
    }
}
