<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      "name" => $this->name,
      "number" => $this->number,
      "program_id" => $this->program_id,
    ];
    // return parent::toArray($request);
  }
}
