<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Speech\V1\SpeechClient;
use App\Models\Transcipt as Transcript;


class TranscriptController extends Controller
{
    public function startTranscriptsProcess(Request $request)
    {
        
        try {

            # Validate the file
            $request->validate([
                'movie_name' => 'required', # File size stated.
                'file' => 'required|mimes:mp4|max:100240', # File size stated.
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

                # Save transcripts to an SRT format
                $srtContent = $this->getSrtContent($transcriptions);

                $srtFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.srt';
                file_put_contents(public_path($srtFileName), $srtContent);

                    if ($srtFileName) {
                        $resData = Transcript::createOrInsert('transcipts');
                        
                        dd($resData);
                    } else {
                        $resData = "Sorry, Wasn't able to save into DB";
                        
                        dd($resData);
                    }

                return response()->download(public_path($srtFileName))->deleteFileAfterSend();
            }

        return response()->json(['error' => 'Transcription failed'], 500);
        } catch (\Throwable $th) {
            throw $th;

            return response()->json(['error' => 'Error processing your request'], 400);
        }


    }



    public function getSrtContent(array $transcriptions)
    {
        $srtContent = '';
        $counter = 1;

        foreach ($transcriptions as $transcription) {
            $srtContent .= $counter++ . PHP_EOL;
            $srtContent .= '00:00:00,000 --> 00:00:01,000' . PHP_EOL; // Adjust timing as needed
            $srtContent .= $transcription . PHP_EOL . PHP_EOL;
        }

        return $srtContent;
    }



}
