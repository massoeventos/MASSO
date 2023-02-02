<?php

namespace Masso\Traits;
use Masso\Product;
use Masso\Behaviors\TagBehavior;
use Masso\Behaviors\UrlBehavior;

trait Filterable{

	public static $sort_translate = ['category' => 'categoria1', 'price' => 'precio2' ];
    public static $stores = ['internet'=>'Web', 'la-serena'=>'La Serena', 'valdivia'=>'Valdivia', 'vitacura'=>'Vitacura'];

    public static function initConditions(){
        return [ ['stock_auxiliar','>','0'], ['precio2','>','0'], ['precio3','>','0'], ['precio2','>','price']];
    }

    public static function withInitConditions(){

        $conditions = Product::initConditions();
        $products = Product::where('code', '!=', '');

        if( !empty($conditions) )
            $products = self::processConditions( $products, $conditions );

        return $products;
    }

    public static function getOrderAvailable(){
    	return ['starred-desc'=>'Destacados', 'category' => 'Categoría', 'name'=>'Nombre', 'price-asc'=>'Precio Menor a Mayor', 'price-desc'=>'Precio Mayor a Menor'];
    }

    public static function getSortField(){

    	$sort_by = session()->get('sort-by', 'starred-desc');

    	if( !empty($_GET['sort-by']) && isset( self::getOrderAvailable()[$_GET['sort-by']] ) )
    		$sort_by = $_GET['sort-by'];

    	$sort_by = explode('-', $sort_by);
    	$field   = isset( self::$sort_translate[$sort_by[0]] ) ? self::$sort_translate[$sort_by[0]] : $sort_by[0];
    	$sort    = (isset( $sort_by[1] ) && in_array($sort_by[1], ['asc', 'desc'])) ? $sort_by[1] : 'ASC';

    	session()->put('sort-by', implode('-', $sort_by) );
    	return $field;
    }

    public static function getSortOrder(){

    	$sort_by = session('sort-by', 'starred-desc');

    	if( !empty($_GET['sort-by']) && isset( self::getOrderAvailable()[$_GET['sort-by']] ) )
    		$sort_by = $_GET['sort-by'];

        $sort_by = explode('-', $sort_by);
        $sort    = (isset( $sort_by[1] ) && in_array($sort_by[1], ['asc', 'desc'])) ? $sort_by[1] : 'ASC';
    	return $sort;
    }

    public static function getFilters( $conditions, $aux = [] ){

        $exceptions = (isset($aux['except'])) ? explode('|', $aux['except']) : [];
        $includes   = (isset($aux['include'])) ? explode('|', $aux['include']) : [];

    	$filters = new \StdClass;
    	$filters->sorts             = self::getOrderAvailable();
    	$filters->actual_sort       = session()->get('sort-by', 'category');

        if( !in_array( 'brands', $exceptions) ):
            $filters->brands            = self::getTagAvailables( 'tag2', $conditions );
            $filters->actual_brand      = request()->query('brand');
        endif;

        if( !in_array( 'stores', $exceptions) ):
            $filters->stores            = self::$stores;
            $filters->actual_store      = request()->query('store');
        endif;

        if( in_array( 'category', $includes) ):
            $filters->categories             = self::getTagAvailables( 'categoria1', $conditions );
            $filters->actual_category      = request()->query('category');
        endif;

        if( in_array( 'subcategory', $includes) ):
            $filters->subcategories             = self::getTagAvailables( 'categoria2', $conditions );
            $filters->actual_subcategory      = request()->query('subcategory');
        endif;

        $another_tags = TagBehavior::generalTags();

        $filters->years             = self::getNameAvailables( 'name', $conditions, $another_tags['years'] );
        $filters->actual_year       = request()->query('year');

        $filters->colors            = self::getNameAvailables( 'name', $conditions, $another_tags['colors'] );
        $filters->actual_color      = request()->query('color');

        $filters->sizes             = self::getNameAvailables( 'name', $conditions, $another_tags['sizes'] );
        $filters->actual_size       = request()->query('size');

        $filters->materials         = self::getNameAvailables( 'name', $conditions, $another_tags['materials'] );
        $filters->actual_material   = request()->query('material');

        $filters->aros              = self::getNameAvailables( 'name', $conditions, $another_tags['aros'] );
        $filters->actual_aro        = request()->query('aro');

        $filters->url = new UrlBehavior;
    	return $filters;
    }

    public static function processConditions( &$products, $conditions ){
        
        $products = $products->whereRaw('precio2 > price');

        foreach( $conditions as $condition )
                if( !is_array($condition[0]) )
                    if( $condition[1] == 'in')
                        $products = $products->whereIn( $condition[0], $condition[2] );
                    else
                        $products = $products->where( $condition[0], $condition[1], $condition[2] );
                else
                    $products = $products->where(function ($query) use ($condition) {
                                    foreach( $condition as $cond )
                                        $query = $query->orWhere( $cond[0], $cond[1], $cond[2] );
                                });

        return $products;
    }

    public static function getProductsResult( $conditions, $aux = [] ){

        if( !empty($aux['order-random']) ):
            $products = Product::inRandomOrder();
        else:
            $order =  self::getSortField();
            if( $order == 'precio2'):
                $products = Product::select(\DB::raw('LEAST(precio2, precio3) as price_order, products.*'))->orderBy( 'price_order', self::getSortOrder()  );
            else:  
                $products = Product::orderBy( self::getSortField(), self::getSortOrder()  );
            endif;
        endif;

        $exceptions = (isset($aux['except'])) ? explode('|', $aux['except']) : [];
        $includes   = (isset($aux['include'])) ? explode('|', $aux['include']) : [];

        if( !empty($conditions) )
            $products = self::processConditions( $products, $conditions );

        if( !empty(request()->query('brand')) && !in_array( 'brands', $exceptions) )
            $products = $products->where('tag2', '=', request()->query('brand'));

        if( !empty(request()->query('category')) && in_array( 'category', $includes) )
            $products = $products->where('categoria1', '=', request()->query('category'));

        if( !empty(request()->query('subcategory')) && in_array( 'subcategory', $includes) )
            $products = $products->where('categoria2', '=', request()->query('subcategory'));

        if( !empty(request()->query('store')) && isset( self::$stores[request()->query('store')] ) 
            && !in_array( 'stores', $exceptions) )
            $products = $products->whereRaw( TagBehavior::getStoreQuery('stores', request()->query('store'), self::$stores) );

        if( !empty(request()->query('year')) && !empty(TagBehavior::isValidQuery('years', request()->query('year'))) )
            $products = $products->whereRaw( TagBehavior::getRawQuery('years', request()->query('year')) );

        if( !empty(request()->query('color')) && !empty(TagBehavior::isValidQuery('colors', request()->query('color'))) )
            $products = $products->whereRaw( TagBehavior::getRawQuery('colors', request()->query('color')) );

        if( !empty(request()->query('size')) && !empty(TagBehavior::isValidQuery('sizes', request()->query('size'))) )
            $products = $products->whereRaw( TagBehavior::getRawQuery('sizes', request()->query('size')) );

        if( !empty(request()->query('material')) && !empty(TagBehavior::isValidQuery('materials', request()->query('material'))) )
            $products = $products->whereRaw( TagBehavior::getRawQuery('materials', request()->query('material')) );

        if( !empty(request()->query('aro')) && !empty(TagBehavior::isValidQuery('aros', request()->query('aro'))) )
            $products = $products->whereRaw( TagBehavior::getRawQuery('aros', request()->query('aro')) );

        if( !empty($aux['order-random']) )
            $products = $products->inRandomOrder();

        if( !empty($aux['limit']) )
            return $products->limit($aux['limit'])->get();

        return $products->paginate( self::$paginate );
    }


    public static function getProductsByBrandConditions( $name ){
        return array_merge(Product::initConditions(),[ ['tag2','=',$name] ]);
    }

    public static function getUsedProductsConditions(){
        return array_merge(Product::initConditions(),[ ['usado','=','1'] ]);
    }

    public static function getHighlightsProductsConditions(){
        return array_merge(Product::initConditions(),[ ['starred','=','1'] ]);
    }

    public static function getSalesProductsConditions(){
        return array_merge(Product::initConditions(),[ ['oferta','=','1'] ]);
    }

    public static function getOffSaleProductsConditions(){
        return array_merge(Product::initConditions(),[ ['oferta','=','4'], ['outlet','=','4'] ]);
    }

    public static function getProductsByCategoryConditions( $name ){
        return array_merge(Product::initConditions(),[ ['categoria1','=', $name] ]);
    }

    public static function getProductsBySubCategoryConditions( $name ){
        return array_merge(Product::initConditions(),[ ['categoria2','=', $name] ]);
    }

    public static function storeConditions( $s ){
        return [ ['stock_auxiliar','>','0'], ['precio2','>','0'], [ ['name', 'LIKE', '%'.$s.'%'], ['code', 'LIKE', '%'.$s.'%'], ['barcode', 'LIKE', '%'.$s.'%'] ] ];
    }

    public static function publicSearch( $code ){

        $order =  self::getSortField();
        if( $order == 'precio2'):
            $products = Product::select(\DB::raw('LEAST(precio2, precio3) as price_order, products.*'))->orderBy( 'price_order', self::getSortOrder()  );
        elseif( $order == 'category'):
            $products = Product::orderBy('starred', 'DESC')->orderBy( self::getSortField(), self::getSortOrder()  );
        else:  
            $products = Product::orderBy( self::getSortField(), self::getSortOrder()  );
        endif;
    
        $conditions = self::initConditions();

        if( !empty($conditions) )
            $products = self::processConditions( $products, $conditions );

        return $products->where('code', $code)->first();

    }

    public static function relatedProducts( $product ){

        $products = Product::orderBy( self::getSortField(), self::getSortOrder()  );
        $conditions = self::initConditions();

        if( !empty($conditions) )
            $products = self::processConditions( $products, $conditions );

        return $products->where('categoria2', $product->categoria2)->limit(5)->get();

    }

    public static function getTagAvailables( $field, $conditions ){
        $tags = Product::orderBy( $field, 'ASC' );

        if( !empty($conditions) )
            $tags = self::processConditions( $tags, $conditions );

        $tags = $tags->groupBy($field)->pluck($field);
        return $tags;
    }

    public static function getNameAvailables( $field, $conditions, $another_conditions ){
        $tags = Product::orderBy( $field, 'ASC' );
        $_tags = [];

        if( !empty($conditions) )
            $tags = self::processConditions( $tags, $conditions );

        foreach( $another_conditions as $key => $condition ):
            $aux = clone $tags;
            $count = $aux->whereRaw($condition['sentence']." '".$condition['search']."'")->count();

            if( $count > 0 )
                $_tags[ $key ] = $condition['name'];

        endforeach;

        return $_tags;
    }

}