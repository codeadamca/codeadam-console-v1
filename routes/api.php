<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\Article;
use App\Models\Assignment;  
use App\Models\Course;
use App\Models\Contribution;
use App\Models\Evaluation;
use App\Models\LivecodeUser;
use App\Models\LivecodeFile;
use App\Models\Meme;
use App\Models\Page;
use App\Models\Social;
use App\Models\Tag;
use App\Models\Tool;
use App\Models\ToolType;
use App\Models\Topic;

use Carbon\Carbon;

use Intervention\Image\Facades\Image;
// use Image;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
|--------------------------------------------------------------------------
| CodeAdam API Calls
|--------------------------------------------------------------------------
*/

Route::get('/image/{slug}', function ($slug) {
  
    $topic = Topic::where('slug', '=', $slug)->first();
  
    if($topic)
    {
      
        $path = public_path().'/storage/'.$topic->image;      
        return response()->file($path);
          
    }
    else
    {
        return array('error' => 'Topic does not exist');
    }
  
});

Route::get('/articles/{filter?}/{value?}', function ($filter, $value) {

    if ($filter == 'type' and $value) 
    {
        $articles = Article::where('article_type_id', $value)->orderBy('published_at', 'DESC')->get();
    }
    elseif ($filter and $value)
    {
        $articles = Article::where($filter, $value)->orderBy('published_at', 'DESC')->get();
    }
    else
    {
        $articles = Article::all();
    }

    foreach($articles as $key => $article)
    {
        if ($articles[$key]->image)
        {
            $articles[$key]->image = env('APP_URL') . 'storage/' . $articles[$key]->image;
        }
        if ($articles[$key]->resources)
        {
            $resources = preg_split('/\r\n|\n\r|\r|\n|,/', $articles[$key]->resources);
            $newResources = array();

            for($i = 0; $i < count($resources); $i += 2)
            {
                $newResources[] = array('name' => $resources[$i], 'url' => $resources[$i + 1]);
            }
            $articles[$key]->resources = $newResources;
        }
    }

    return $articles;


})->where('filter', 'type|home')->where('value', 'yes|no|[0-9]+');



Route::get('/evaluations', function () {

    return Evaluation::all();

});


Route::get('/tags', function () {

    return Tag::orderBy('title')->get();

});


Route::get('/memes/tag/{tag?}', function (Tag $tag) {

    $memes = $tag->manyMemes()->orderBy('displayed_at')->get();

    foreach($memes as $key => $meme)
    {
        if ($memes[$key]->image)
        {
            $memes[$key]->image = env('APP_URL') . 'storage/' . $memes[$key]->image;
        }
    }

    return $memes;

})->where('type', '[0-9]+');


Route::get('/memes/displayed/{meme?}', function (Meme $meme) {

    $meme->displayed_at = Carbon::now();
    $meme->save();
 
    return $meme;

})->where('type', '[0-9]+');



Route::get('/pages/topic/{topic?}', function (Topic $topic) {

    $pages = Page::where('topic_id', $topic->id)->orderBy('published_at', 'ASC')->get();

    foreach($pages as $key => $page)
    {
        if ($pages[$key]->image)
        {
            $pages[$key]->image = env('APP_URL') . 'storage/' . $pages[$key]->image;
        }

        $pages[$key]->topics = $page->manyTopics()->get();
        foreach($pages[$key]->topics as $key2 => $topic)
        {
            $pages[$key]->topics[$key2]->image = env('APP_URL') . 'storage/' . $pages[$key]->topics[$key2]->image;
        }

    }

    return $pages;

});

Route::get('/pages/profile/{slug}', function ($slug) {

    $page = Page::where('id', $slug)->orWhere('slug', $slug)->firstOrFail();

    if ($page->image)
    {
        $page->image = env('APP_URL') . 'storage/' . $page->image;
    }

    $page->topic = $page->topic()->first();
    
    if ($page->topic->image)
    {
        $page->topic->image = env('APP_URL') . 'storage/' . $page->topic->image;
    }
    if ($page->topic->banner)
    {
        $page->topic->banner = env('APP_URL') . 'storage/' . $page->topic->banner;
    }

    $page->topics = $page->manyTopics()->get();
    foreach($page->topics as $key => $topic)
    {
        if ($page->topics[$key]->image)
        {
            $page->topics[$key]->image = env('APP_URL') . 'storage/' . $page->topics[$key]->image;
        }
        if ($page->topics[$key]->banner)
        {
            $page->topics[$key]->banner = env('APP_URL') . 'storage/' . $page->topics[$key]->banner;
        }
    }

    return $page;

});



Route::get('/socials/{filter?}/{value?}', function ($filter = false, $value = false) {

    if ($filter and $value) 
    {
        $socials = Social::where($filter, $value)->orderBy('title')->get();
    }
    else
    {
        $socials = Social::orderBy('title')->get();
    }

    foreach($socials as $key => $social)
    {
        if ($socials[$key]->image)
        {
            $socials[$key]->image = env('APP_URL') . 'storage/' . $socials[$key]->image;
        }
    }

    return $socials;

})->where('filter', 'home|about|header')->where('value', 'yes|no');


Route::get('/toolTypes', function() {

    return ToolType::all();

});

Route::get('/tools/type/{toolType?}', function (ToolType $toolType) {

    $tools = Tool::where('tool_type_id', $toolType->id)->orderBy('title')->get();

    foreach($tools as $key => $tool)
    {
        if ($tools[$key]->image)
        {
            $tools[$key]->image = env('APP_URL') . 'storage/' . $tools[$key]->image;
        }
    }

    return $tools;

})->where('type', '[0-9]+');



Route::get('/topics/{filter?}/{value?}', function ($filter = null, $value = null) {

    if ($filter and $value)
    {
        if ($filter == 'pages')
        {
            $topics = Topic::whereHas('pages')->orderBy('title')->get();
        }
        else
        {
            $topics = Topic::where($filter, $value)->orderBy('title')->get();
        }
    }
    else 
    {
        $topics = Topic::orderBy('title')->get();
    }

    foreach($topics as $key => $topic)
    {
        if ($topics[$key]->image)
        {
            $topics[$key]->image = env('APP_URL') . 'storage/' . $topics[$key]->image;
        }
        $topics[$key]->pages = $topic->pages()->count();
    }

    return $topics;

})->where('filter', 'pages|teaching|background')->where('value', 'yes|no|light|dark');

Route::get('/topics/page/{page?}', function (Page $page) {

    $topics = $page->manyTopics()->get();

    foreach($topics as $key => $topic)
    {
        if ($topics[$key]->image)
        {
            $topics[$key]->image = env('APP_URL') . 'storage/' . $topics[$key]->image;
        }
    }

    return $topics;

});

Route::get('/assignments', function () {

    $assignments = Assignment::all();

    foreach($assignments as $key => $assignment)
    {
        if ($assignments[$key]->image)
        {
            $assignments[$key]->image = env('APP_URL') . 'storage/' . $assignments[$key]->image;

            $assignments[$key]->topics = $assignment->manyTopics()->get();
            foreach($assignments[$key]->topics as $key2 => $topic)
            {
                $assignments[$key]->topics[$key2]->image = env('APP_URL') . 'storage/' . $assignments[$key]->topics[$key2]->image;
            }
        }
    }

    return $assignments;

});

Route::get('/courses', function () {

    $courses = Course::orderBy('name', 'ASC')->get();

    foreach($courses as $key => $course)
    {
        $courses[$key]->topics = $course->manyTopics()->get();
        foreach($courses[$key]->topics as $key2 => $topic)
        {
            $courses[$key]->topics[$key2]->image = env('APP_URL') . 'storage/' . $courses[$key]->topics[$key2]->image;
        }

    }

    return $courses;

});

/*
|--------------------------------------------------------------------------
| GitHub Contributions API Calls
|--------------------------------------------------------------------------
*/

Route::post('/contributions/store', function () {

    if(!request()->exists('github') or !request()->exists('referer'))
    {
        return array('status' => 'error');
    }

    $check = Contribution::where('github', request()->post('github'))->count();

    if($check > 0)
    {
        $contribution = Contribution::where('github', request()->post('github'))->first();
        $contribution->count ++;
    }
    else
    {
        $contribution = new Contribution();
        $contribution->count = 1;
        $contribution->github = request()->post('github');
    }

    $contribution->referer = request()->post('referer');
    $contribution->save();

    return $contribution->toArray();

});

/*
|--------------------------------------------------------------------------
| LiveCode API Calls
|--------------------------------------------------------------------------
*/

Route::get('/livecode/users', function () {

    $filter = \Carbon\Carbon::now()->subDays(21);

    $users = LivecodeUser::where('updated_at', '>', $filter)->orderBy('github')->get();

    foreach($users as $key => $user)
    {
        $users[$key]->files = $user->files()->count();
        $users[$key]->image = 'https://avatars.githubusercontent.com/'.$user->github;
      
        if($user->files()->count() == 0)
        {
            $users[$key]->status = 'Inactive';
        }
        else
        {
            $file = $user->files()->orderBy('updated_at', 'DESC')->first(); 
            $users[$key]->status = $file->updated_at->diffInHours() < 2 ? 'Now Coding' : $file->updated_at->diffForHumans();
        }
        
    }

    return $users;

});

Route::get('/livecode/code', function () {
  
    $check = LivecodeUser::where('github', request()->get('github'))->count();
  
    if($check > 0)
    {
      
        $user = LivecodeUser::where('github', request()->get('github'))->first();
        $files = LivecodeFile::where('livecode_user_id', $user->id)->get();
      
        foreach($files as $key => $file)
        {
            $files[$key]['content'] = htmlentities($file['content']);
        }
      
        return $files;

    }
    else
    {
        return array('status' => 'error');
    }
  
});

Route::post('/livecode/save', function () {

    if(!request()->exists('github') or 
        !request()->exists('display') or 
        !request()->exists('content') or 
        !request()->exists('path'))
    {
        return array('status' => 'error');
    }

    $check = LivecodeUser::where('github', request()->post('github'))->count();

    if($check > 0)
    {
        $user = LivecodeUser::where('github', request()->post('github'))->first();
        $user->count ++;
        $user->display = request()->post('display');
        $user->save();

        $user_id = $user->id;
    }
    elseif(request()->post('github'))
    {
        $user = new LivecodeUser();
        $user->count = 1;
        $user->github = request()->post('github');
        $user->display = request()->post('display');
        $user->save();

        $user_id = $user->id;
    }
    else
    {
        $user_id = 0;
    }

    $check = LivecodeFile::where('path', request()->post('path'))
        ->where('livecode_user_id', $user_id )
        ->count();

    if($check > 0)
    {
        $file = LivecodeFile::where('path', request()->post('path'))
            ->where('livecode_user_id', $user_id )
            ->first();
    }
    else
    {
        $file = new LivecodeFile();
        $file->livecode_user_id = $user_id;
        $file->path = request()->post('path');
    }

    $file->content = request()->post('content');
    $file->save();

    return array('status' => 'complete');

});

Route::post('/livecode/reset', function () {

    $check = LivecodeUser::where('github', request()->post('github'))->count();

    if($check > 0)
    {
        $user = LivecodeUser::where('github', request()->post('github'))->first();
        $user->count ++;
        $user->save();

        $user_id = $user->id;
    }
    else
    {
        $user_id = 0;
    }

    LivecodeFile::where('livecode_user_id', $user_id)->delete();

    return array('status' => 'complete');

}); 