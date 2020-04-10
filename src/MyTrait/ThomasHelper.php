<?php


namespace Thomas\Bundle\MyTrait;


trait ThomasHelper
{
    protected $paginate_size = 5;
    protected $search, $per_page, $sort, $sort_by, $check_relation,$paginate_on;

    protected function ThomasApi($model, $search_columns = null)
    {
        //Request Check;
        $this->CheckPerPage();
        $this->CheckSearch();
        $this->CheckSort();
        $this->CheckSortBy();
        $this->CheckRelation();
        $this->CheckPaginateOn();

        //Closuer Setup
        $search = $this->search;
        $check_relation = $this->check_relation;

        //when search has value
        $main_query = $model->when($search, function ($query, $search) use ($search_columns) {
            $default_search = config('thomas.default_search');
            $search_col = $search_columns ? array_merge($search_columns, $default_search) : $default_search;

            //return the search value
            return $query->whereLike($search_col, $search);

        })->when($check_relation, function ($query) {

            //return the relation sort value
            return $query->with([$this->check_relation[0] => function ($sub_query) {
                $sub_query->orderBy($this->check_relation[1], $this->sort);
            }]);
        }, function ($query) {
            //return with default sort
            return $query->orderBy($this->sort_by, $this->sort);
        });

//        return $this->paginate_on;
        //check pagination option
        if ($this->paginate_on){
            return $main_query->paginate($this->paginate_size);
        }else{
            $allProperties = collect();
            $main_query->chunk(100,function ($data_chunk) use ($allProperties){
                foreach ($data_chunk as $item){
                    $allProperties->push($item);
                }
            });


            return ['data'=>$allProperties];
//            return response(['data'=>$allProperties]);
//            return $allProperties;


//            return $main_query->get();
        }

    }

    private function CheckPerPage()
    {
        $this->per_page = \request()->has('per_page') ? request('per_page') == "All" ? $this->paginate_size = 1000 : $this->paginate_size = \request('per_page') : $this->per_page;
    }

    private function CheckSearch()
    {
        $this->search = \request('search') ?? null;
    }

    private function CheckSort()
    {
        $this->sort = \request('sort') ?? 'desc';
    }

    private function CheckSortBy()
    {
        $this->sort_by = \request('sort_by') ?? 'id';
    }
    private function CheckPaginateOn()
    {
        $this->paginate_on = \request('paginate') ?? true;
    }

    private function CheckRelation()
    {
        $this->check_relation = \Str::contains($this->sort_by, '.') ? explode('.', $this->sort_by) : null;
    }
}
