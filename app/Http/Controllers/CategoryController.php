<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Validation\Validator;

class CategoryController extends Controller
{
    public function categorylist(){
        return view('category/list');
    }

    public function categoryAll(){
        $categories = Category::orderBy('id','asc')->paginate(3);

        return view('admin.category.category_list',compact('categories'));
    }

    public function creation(Request $req){
        // dd('hi');
        // dd($req->all());
        $this->validation($req);
        $datapack = $this->myArray($req);
        Category::create($datapack);
        return redirect()->route('category_list')->with(['categoryCreated'=>'A New Category is Created.']);
    }

    public function deleteCategory($id){
        Category::where('id',$id)->delete();
        return redirect()->route('category_list')->with(['categoryDeleted'=>'The Category is Deleted.']);
    }

    public function editCategory($id){
        $category = Category::where('id',$id)->first();
        return view('admin.category.category_edit',compact('category'));
    }

    public function updateCategory(Request $req,$id){
        $this->validation($req);

        $myArray = $this->myArray($req);
        Category::where('id',$id)->first()->update($myArray);
        // dd($datapack);
        return redirect()->route('category_list')->with(['categoryUpdated'=>'The Category is Updated.']);
        // return view('updateCategory',compact('datapack'));
    }

    public function viewCategory($id){
        $datapack = Category::where('id',$id)->first()->toArray();
        dd($datapack);
    }

    public function searchCategory(){
        $categories = Category::when($_REQUEST['search'],function($item){
            $searchkey = $_REQUEST['search'];
            $item -> where('name','like','%'.$searchkey.'%');
        })
        ->orderBy('id','asc')
        ->paginate(3);

        $categories->appends(request()->all()); //  <-------------------------------------------PAG
        return  view('admin.category.category_list',compact('categories'));
    }




    private function myArray($data){
        $datapack = [
            "name" => $data->categoryName
        ];
        return $datapack;
    }

    private function validation($data){
        $validateItem = [
            // "categoryName" => "required|unique:categories,name,".$data->id
            'categoryName' => ['required',Rule::unique('categories', 'name')->ignore($data->id)]
        ];
        $validateMsg = [
            "categoryName.required" => "You need to fill in Name field.",
            "categoryName.unique" => "The category already exist."
        ];
        Validator::make($data->all(),$validateItem,$validateMsg)->validate();
    }

    public function categoryAdd(Request $req){
        $data = [
            "name" => $req->name,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now()
        ];
        $all = Category::create($data);

        return response()->json($all,200);
    }

    public function categoryRetrieve(){
        $data = Category::get();
        return response()->json($data,200);
    }


    public function deleteCategoryAPI(Request $req){
        $data = Category::where('id',$req->id)->first();

        if(isset($data)){
            $data->delete();
            return response()->json(['status'=>'true','message'=> 'Row has been deleted.']);
        }else{
            return response()->json(['status'=>'false','message'=>'Row does not exist.']);
        };
    }

    public function seeCategory($name,$email){
        // return [
        //     "name" => request('name'),
        //     "email" => request('email')
        // ];

        return [
            "name" => $name,
            "email" => $email
        ];
    }

    public function categoryView($id){
        $data = Category::where('id',$id)->first();
        if(isset($data)){
            $response = [
                'status' => 'true',
                'data' => $data
            ];
            return response()->json($response,200);

        }else{
            $response = [
                'status' => 'false',
                'data' => 'ID not found.'
            ];
            return response()->json($response,404);
        }
    }

    public function categoryUpdate(Request $req){
        $data = Category::where('id',$req->id)->first();
        if(isset($data)){
            $data->update([
                "name" => $req->name,
                "updated_at" => Carbon::now()
            ]);

            $response = Category::where('id',$req->id)->first();
            // dd($response);
            return response()->json($response,200);
        }else{
            $response = [
                "message" => "Not Found"
            ];
            return response()->json($response,404);
        }
    }
}
