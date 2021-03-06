<?php
namespace App\Library;

use App\Models\Content;
use App\Models\ContentType;
use App\Models\TypeProduct;
use App\Models\Product;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class QueryLibrary
{
    public function contentNameGet($name) {
        $data = ContentType::where('content_types.name', $name)
        ->join('contents', 'content_types.id', '=', 'contents.content_types_id')
        ->where('content_types.active', 1)
        ->select('contents.id as id', 'title', 'subtitle', 'description', 'image', 'icon', 'url', 'class', 'position')
        ->get();
        return $data;
    }
    public function contentNameFirst($name) {
        $data = ContentType::where('content_types.name', $name)
        ->join('contents', 'content_types.id', '=', 'contents.content_types_id')
        ->where('content_types.active', 1)
        ->select('contents.id as id', 'title', 'subtitle', 'description', 'image', 'icon', 'url', 'class', 'position')
        ->first();
        return $data;
    }

    public function contentGet($id) {
        $data = ContentType::where('content_types.id', $id)
        ->join('contents', 'content_types.id', '=', 'contents.content_types_id')
        ->where('content_types.active', 1)
        ->select('contents.id as id', 'title', 'subtitle', 'description', 'image', 'icon', 'url', 'class', 'position')
        ->get();
        return $data;
    }
    public function contentFirst($id) {
        $data = ContentType::where('content_types.id', $id)
        ->join('contents', 'content_types.id', '=', 'contents.content_types_id')
        ->where('content_types.active', 1)
        ->select('contents.id as id', 'title', 'subtitle', 'description', 'image', 'icon', 'url', 'class', 'position')
        ->first();
        return $data;
    }
    public function contentIn($id) {
        $data = ContentType::whereIn('content_types.id', $id)
        ->join('contents', 'content_types.id', '=', 'contents.content_types_id')
        ->where('content_types.active', 1)
        ->select('contents.id as id', 'title', 'subtitle', 'description', 'image', 'icon', 'url', 'class', 'position')
        ->get();
        return $data;
    }
    public function productTypeGet() {
        $data = TypeProduct::get();
        return $data;
    }
    public function productGet($id, $show) {
        $data = Product::where('type_products_id', $id)->take($show);
        return array($data->get(), $data->count());
    }
}
