<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleDetail extends Model
{
    protected $fillable = ['article_id'];

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
}
