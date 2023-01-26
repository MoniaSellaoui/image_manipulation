<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\ImageManipulation;
use App\Http\Requests\ResizeImageRequest;
use App\Http\Requests\UpdateImageManipulationRequest;

class ImageManipulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return ImageManipulationResurce::collection(ImageManipulation::paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreImageManipulationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function resize(ResizeImageRequest $request)
    {
        //
        $all=$request->all();
        $image=$all['image'];
        unset($all['image']);
        $data[
            'type'=>ImageManipulation::TYPE_RESIZE,
            'data'=>json-encode($all),
            'user_id'=>null
        ];
        if(isset($all['album_id']))
        {//TODO
            $data['album_id']=$all['album_id'];
        }
$dir='images/'.str::random().'/';
$absolutePath= public_path($dir);
File::makeDirectory($absolutePath);




    if($image instanceof UploadedFile)
    {
        $data['name']=$image->getClientOriginalName();
        //image.jpg->image.resized.jpg
        $filename=pathinfo($data[name],PATHINFO_FILENAME);
        $extention=$image->getClientOriginalExtension(); 
        $originalPath=$absolutePath.$data['name'];
        $image=move($absolutePath,$data['name']);
      

    }else
    {
$data['name']=pathinfo($image,PATHINFO_BASENAME);
$filename=pathinfo($image,PATHINFo_FILENAME); 
$extension=pathinfo($image,PATHINFo_EXTENSION);
$originalPath=$absolutePath.$data['name'];
copy($image,$originalPath);



    }
    $data['path']=$dir.$data['name'];

    //resize
    $w=$all['w'];
    $h=$all['h']??false;
    list($width,$height,$image)=$this->getImageWidthAndHeight($w,$h,$originalPath);
    $resizedFilename=$filename.'-resized-'.$extension;
    $image->resize($width,$height)->save($absolutePath,$resizedFilename);
    $data['output_path']=$dir.$resizedFilename;
    $imageManipulation=ImageManipulation::create($data);
    return  new ImageManipulationResource($imageManipulation);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */

public function byAlbum(Album $album)
{$where=[
   'album_id' =>$album->id,
];
    return ImageManipulationResurce::collection(ImageManipulation::where($where)->paginate());
}

    public function show(ImageManipulation $image)
    {
        //
        return new ImageManipulationResource($image);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateImageManipulationRequest  $request
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
   

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImageManipulation $image)
    {
        //
        $image->delete();
        return response('',204);
    }
    protected function getImageWidthAndHeight($w,$h,string $originalPath)
    {
        $image=Image::make($originalPath);
        $originalWidth=$image->width();
        $originalHeight=$image->height();
        
        if(str_ends_with($w,'%'))
        {
            $ratioW=(float)str_replace('%','','$w');
            $ratioH=$h?(float)str_replace('%','','$h'): $ratioW;

            $newWidth=$originalWidth*$ratioW/100;
            $newHeight=$originalHeight*$ratioH/100;


        }else
        {
            $newWidth=(float)$w;
            $newHeight=$h?(float)$h:$originalHeight*$newWidth/$originalWidth;

        }
        return[$newWidth,$newHeight];

    }
}
