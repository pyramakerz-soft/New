<div>
    <h1>Game Images</h1>
    <div class="image-gallery">
        @if ($images->isEmpty())
            <p>No images found.</p>
        @else
            @foreach ($images as $image)
                <div class="image-item">
                    <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->name }}">
                    <p>{{ $image->name }}</p>
                </div>
            @endforeach
        @endif
    </div>
</div>

<style>
    .image-gallery {
        display: flex;
        flex-wrap: wrap;
    }

    .image-item {
        margin: 10px;
        text-align: center;
    }

    .image-item img {
        max-width: 100px;
        max-height: 100px;
    }
</style>
