<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'author',
        'number_of_pages',
        'description',
        'rating',
        'category',
        'cover',
        'path',
    ];
    protected $allowedFilters = [
        'title' => ['l'],
        'author'=> ['l'],
        'description'=>['l'],
        'category'=>['eq'],
        'number_of_pages'=>['lt','gt','lte','gte','eq'],
        'rating'=>['lt','gt','lte','gte','eq']
    ];
    protected $operatorsCasts = [
        'eq' => '=',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>=',
        'l' => 'like'
    ];
    public function scopeFilters(Builder $res , Request $request) {
        $queries = [];
        foreach($this->allowedFilters as $filter => $operators)
        {
            $query = $request->query($filter);
            if(isset($query))
            {
                foreach ($operators as $operator) {
                    if(isset($query[$operator]))
                    {
                        $value = $query[$operator];
                        if($operator == 'l')
                            $value = '%' . $value . '%';
                        $queries[]=[$filter,$this->operatorsCasts[$operator],$value];
                    }
                }
            }
        }
        $res->where($queries);
    }
    public function users() {
        return $this->belongsToMany(User::class);
    }
    public function challenges() {
        return $this->belongsToMany(Challenge::class);
    }
}
