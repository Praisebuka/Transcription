<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MozillaDeepspeech\Deepspeech\Deepspeech;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Transcipt;

class DeepSpeechController extends Controller
{
    
    
    public function transcribeAndSaveSrt($videoFilePath)
    {
        // Configure DeepSpeech
        $deepspeech = new Deepspeech([
            'model' => '/path/to/your/deepspeech/model.pbmm',
            'scorer' => '/path/to/your/deepspeech/scorer.scorer',
            'audio' => storage_path("app/public/{$videoFilePath}"),  
        ]);

        // Perform speech-to-text transcription
        $transcript = $deepspeech->recognize();
 
        // Save transcript to the database
        $this->saveToDatabase($transcript);

        // Save transcript to an SRT file
        $srtFilePath = $this->saveToSrtFile($transcript);

        // Download the SRT file
        $this->downloadSrtFile($srtFilePath);
    } 

    public function saveToDatabase($transcript)
    {
        // Save transcript to the "Transcripts" table
        Transcipt::table('transcripts')->insert([
            'content' => $transcript,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function saveToSrtFile($transcript)
    {
        // Generate unique filename
        $srtFileName = 'transcript_' . time() . '.srt';

        // Save transcript to an SRT file
        Storage::disk('local')->put($srtFileName, $transcript);
 
        // Return the file path
        return $srtFileName;
    }

    public function downloadSrtFile($srtFilePath)
    {
        // Set appropriate headers for file download 
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $srtFilePath);
        header('Content-Length: ' . filesize(storage_path("app/public/{$srtFilePath}")));

        // Read and output the file content
        readfile(storage_path("app/public/{$srtFilePath}"));
    } 


    
    // Example usage
    // $this->transcribeAndSaveSrt('path/to/your/video.mp4');

}
