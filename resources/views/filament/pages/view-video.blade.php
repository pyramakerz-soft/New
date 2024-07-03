<x-filament::page>
   
    <video autoplay controls>
        <source src="{{ asset('storage/' . $this->record->video) }}" type="{{ $this->record->type }}">
    </video>
</x-filament::page>
