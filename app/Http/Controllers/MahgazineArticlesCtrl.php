<?php

namespace App\Http\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\MahgazineArticle;

class MahgazineArticlesCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }
    
    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'order';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = MahgazineArticle::orderBy($order, $direction);

        if($request->trash){
            $query->onlyTrashed();
        }else if($request->withTrashed){
            $query->withTrashed();
        }

        if($request->filter){
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->filter . '%');
                $q->orWhere('description', 'LIKE', '%' . $request->filter . '%');
                $q->orWhere('created_at', 'LIKE','%'.$request->filter.'%');

                $q->orWhereHas('hotel', function ($q2) use ($request) {
                    $q2->where('name', 'LIKE', '%' . $request->filter . '%');
                });
            });
        }

        if($request->filterBySection){
            $query->where('mahgazine_edition_section_id', $request->filterBySection);
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(MahgazineArticle $article){
        return $article->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(MahgazineArticle $article, Request $request){
        return $this->saveData($article, $request)->_data();
    }

    public function delete($id, Request $request){
        $article = MahgazineArticle::withTrashed()->find($id);
        if($article){
            if($request->forceDelete){
                $denials = $article->_deleteAllowed();
                if(!count($denials)) {
                    $article->forceDelete();
                    $article->_afterDelete();
                }else{
                    throw new HttpResponseException(
                        response()->json(['errors' => $denials], 400)
                    );
                }
            }else{
                $article->delete();
            }
            return $article->_data();
        }
        return array();
    }

    public function restore($id){
        $article = MahgazineArticle::withTrashed()->where('id', $id)->first();
        if($article){
            $article->restore();
            return $article->_data();
        }
        return array();
    }
    
    public function order(MahgazineArticle $article, $direction){
        if( ! ($direction == 1 || $direction == -1) ){ exit; } // filter direction to 1 or -1

        $section = $article->section;
        $section_articles = $section->articles()->orderBy('order', 'asc')->get();

        $update_articles = [];
        $order_numbers = [];

        // prepare ordering numbers
        if(count($section_articles)){
            foreach($section_articles as $k => $art){
                if(!in_array($art->order, $order_numbers)){
                    $order_number = $art->order;
                }else{
                    $order_number = $section_articles[$k - 1]->order + 1;
                }

                $order_numbers[] = $order_number;
                $update_articles[] = [
                    'id' => $art->id,
                    'order' => $order_number,
                ];
            }
        }

        // find and replace according to direction
        if(
            ($direction == -1 && !($article->id == $update_articles[0]['id'])) ||
            ($direction == 1 && !($article->id == $update_articles[count($update_articles)-1]['id']))
        ){
            foreach($update_articles as $k => $article_data){
                if($article->id == $article_data['id']){
                    $article_data_number = $article_data['order'];

                    $update_articles[$k]['order'] = $update_articles[$k + $direction]['order'];
                    $update_articles[$k + $direction]['order'] = $article_data_number;
                }
            }

            // updates new order to database
            foreach($update_articles as $article_data){
                $order = MahgazineArticle::find($article_data['id']);
                if($order){
                    $order->order = $article_data['order'];
                    $order->save();
                }
            }
        }
    }

    private function saveData($article, $request){
        if(!$article){
            $article = new MahgazineArticle;

            $last_insert_article = MahgazineArticle::where('mahgazine_edition_section_id', $request->mahgazine_edition_section_id)
                ->orderBy('order', 'desc')
                ->first();

            $order_number = ($last_insert_article) ? $last_insert_article->order + 1 : 1;
            $article->order = $order_number;
        }
        $this->validateData($request);

        $article->fill($request->all());
        $article->save();

        return $article;
    }


    private function validateData($request){
        $rules = [
            'name' => ['required'],
            'description' => ['required'],
        ];

        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }
    }
}
