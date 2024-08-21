<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\StudentLock;
class UnitResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
      $arr = array();
      
      foreach($this->resource as $dt){
      $arr[] = [
          'id' => $dt->id,
          'name' =>$dt->name,
          'number' => $dt->number,
          'program_id' =>$dt->program_id,
          'image' => $dt->image,
          'created_at' =>$dt->created_at,
          'updated_at' => $dt->updated_at,
          'is_active' => StudentLock::where('student_id',auth()->user()->id)->where('unit_id',$dt->id)->exists() ? 0 : 1,
          ];     
      }
    return $arr;
    // return parent::toArray($request);
  }
}
