<?php


namespace Thomas\Bundle\MyTrait;


trait ThomasHelper
{
    protected function ThomasApi($model, $search_columns = null)
    {
        $paginate_size = 5;
//        \request()->has('per_page')? $paginate_size=\request('per_page'):'';
        \request()->has('per_page') ? request('per_page') == "All" ? $paginate_size = 1000 : $paginate_size = \request('per_page') : '';

        $search = \request('search');
        $sort = \request('sort') ?? 'desc';
        $sort_by = \request('sort_by') ?? 'id';
        $check_relation = \Str::contains($sort_by, '.') ? explode('.', $sort_by) : null;

        return $model->when($search, function ($q, $search) use ($sort, $sort_by, $search_columns) {
            $default_col = ['name', 'language.chinese', 'language.myanmar'];
            $search_col = $search_columns ? array_merge($search_columns, $default_col) : $default_col;
            return $q->whereLike($search_col, $search);
        })->when($check_relation, function ($query, $sort_by) use ($sort) {
            return $query->with([$sort_by[0] => function ($or) use ($sort_by, $sort) {
                $or->orderBy($sort_by[1], $sort);
            }]);
        })->when($sort && !$check_relation, function ($q, $sort) use ($sort_by) {
            return $q->orderBy($sort_by, request('sort'));
        })
            ->paginate($paginate_size);
    }
}
