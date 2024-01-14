<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Speech\V1\SpeechClient;
use App\Models\Transcipt as Transcript;
use Illuminate\Support\Facades\Log;

class TranscriptController extends Controller
{

    # Using Google Translator 

    public function startTranscriptsProcess(Request $request)
    {
        
        try {

            #  The file
            $request->validate([
                'movie_name' => 'required', # File size stated.
                'file' => 'required|mimes:mp4|max:100240', # File size stated.
            ], [
                'movie_name.required' => 'Please provide a movie name.',
                'file.required' => 'Please upload a file.',
                'file.mimes' => 'The file must be in MP4 format.',
                'file.max' => 'The file size must not exceed 100240 kilobytes.',
            ]);

            $file = $request->file('file'); 
            $movieName = $request->file('movie_name');

            # Configure the Speech-to-Text client 
            $speech = new SpeechClient([
                'credentials' => json_decode(file_get_contents(env('GOOGLE_APPLICATION_CREDENTIALS')), true),
            ]);

            # The language to use
            $config = [ 'language_code' => 'en-US' ];

            # Start the transctiption  
            $operation = $speech->longRunningRecognize( $config, file_get_contents($file->getPathname()));

            $operation->pollUntilComplete();

            if ($operation->operationSucceeded()) {
                $response = $operation->getResult();

                # Get transcription.
                $transcriptions = [];
                foreach ($response->getResults() as $result) {
                    foreach ($result->getAlternatives() as $alternative) {
                        $transcriptions[] = $alternative->getTranscript();
                    }
                }

                dd('got here');
                # Save transcripts to an SRT format
                $srtContent = $this->getSrtContent($transcriptions);

                $srtFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.srt';
                file_put_contents(public_path($srtFileName), $srtContent);

                    if ($srtFileName) {
                        $resData = Transcript::create([ 
                            'movie_name' => $movieName,
                            'file' => $file,
                        ]);
                        
                        dd($resData);
                    } else {
                        $resData = "Sorry, Wasn't able to save into DB";
                        
                        dd($resData);
                    }

                return response()->download(public_path($srtFileName))->deleteFileAfterSend();
            }

        return response()->json(['error' => 'Transcription failed'], 500);
        } catch (\Throwable $th) {

            Log::error('Error processing transcription request: ' . $th->getMessage());

            return response()->json(['Error' => $th->getMessage()], 400);
        }


    }

    public function getSrtContent(array $transcriptions)
    {

        $srtContent = '';
        $counter = 1;

        foreach ($transcriptions as $transcription) {
            $srtContent .= $counter++ . PHP_EOL;
            $srtContent .= '00:00:00,000 --> 00:00:01,000' . PHP_EOL; # Adjust timing as needed
            $srtContent .= $transcription . PHP_EOL . PHP_EOL;
        }

        return $srtContent;
    }


    # Using Mozilla Free Source
    public function mozillaProcess(Request $request) 
    {
         
    }

 
    

}
