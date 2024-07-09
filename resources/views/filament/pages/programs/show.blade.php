 <?php
    $programs = App\Models\Program::where('stage_id', $_GET['programs'])
        ->with(['course', 'stage'])
        ->get();
        
    ?>
    <div style="display: flex; flex-direction: row;flex-wrap: wrap">
        <?php
   
    foreach ($programs as $index => $program ){ ?>
        <div class="aho" style="width: 50%; text-align: center">
            <a href="{{ url("admin/programs/program/{$program->id}/view") }}" class="program-container" style="width: 50%">
                <img src="{{ asset("storage/$program->image") }}" alt="{{ $program->image }}">
                <?php echo $program->course->name; ?>
            </a>
        </div>

        <?php 
} ?>
        <div>
            <style>
                /* .program-container {
                    text-align: center
                }

                .program-container img {
                    margin: auto;

                } */
            </style>
