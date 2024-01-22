<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Transciption Page</title>
</head>
<body>
    
    <div class="section secondary">
        <p class="bg-red">
            Found your page :)
        </p>
    </div>


            <!-- Using Tailwind CSS classes -->
        <div class="bg-blue-500 text-white p-4">
            This is styled with Tailwind CSS.
        </div>

        <!-- Using Bootstrap classes -->
        <div class="alert alert-primary">
            This is styled with Bootstrap.
        </div>


        <form action="{{ route('get started') }}" method="POST" enctype="multipart/form-data"> 
            @csrf

            <p>Please add your movie MP4 file here</p>
            <input type="file" name="file" accept=".mp4" maxlength="100240" required>

            <p>What's the name of the movie you've added?</p>
            <input type="text" name="movie_name" placeholder="your movie name is?" required>

            <button type="submit">Get The SRT</button>

        </form>


 
</body>
</html>