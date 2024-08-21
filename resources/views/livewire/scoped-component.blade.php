    {{-- <h1>Programs</h1> --}}
    <?php
    $programs = App\Models\Program::where('stage_id', $_GET['programs'])
        ->with(['course', 'stage'])
        ->get();
    
    ?>
    <?php
    foreach ($programs as $index => $program ){ ?>
    <div id="scope-{{ $cssScope }}">

        <div  wire:loading>Program name...</div>
        <div wire:loading.remove>

            <?php echo htmlentities($program->name); ?>

        
        </div>


        <div>
        <div wire:loading>Course name...</div>
        <div wire:loading.remove>
            <?php echo htmlentities($program->course->name); ?>
        </div>


        <div>
            <div wire:loading>Stage name...</div>
            <div wire:loading.remove>
                <?php echo htmlentities($program->stage->name); ?>
            
    </div>

    <style>
        /* you will need to apply scopes yourself manually */
        #scope-{{ $cssScope }} div:first-child {
            color: red;
        }
    
        
        </style>
    <?php 
} ?>
