<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

class laguController extends Controller
{
    public function upload(Request $request) {
        //validate fil yg di up, hrs pdf dengan max size 10mb
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:10240',
        ]);
        
        $pdfFile = $request->file('pdf'); //get pdf yg di up
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfFile->getRealPath()); //parse pdf untuk ambil content
        $text = $pdf->getText(); //extract isi pdf

        $laguArray = explode('--', $text);  //separate tiap lagu
        $dataLagu = []; //create array untuk simpan lagu

        foreach ($laguArray as $laguText) {
            preg_match('/judul:\s*(.*)/i', $laguText, $judul); //extract judul
            preg_match('/penyanyi:\s*(.*)/i', $laguText, $penyanyi); //extract penyanyi
            preg_match('/lirik:\s*(.*)/is', $laguText, $lirik); //extract lirik
            
            //save ke array $dataLagu jika terdapat judul, penyanyi, dan lirik
            if ($judul && $penyanyi && $lirik) {
                $dataLagu[] = [
                    'judul' => trim($judul[1]),
                    'penyanyi' => trim($penyanyi[1]),
                    'lirik' => trim($lirik[1]),
                ];
            }
        }

        //save $dataLagu ke session
        session(['lagu_data' => $dataLagu]);

        return view('cari', ['lagus' => $dataLagu]);
    }
    
    public function search(Request $request) {
        $query = $request->input('query'); //get query dari form
        $laguData = session('lagu_data', []); //get data lagu yg disimpan

        //jika tidak ada lagu yg tersimpan
        if (empty($laguData)) {
            return redirect('/upload')->with('error', 'Upload dulu file PDF berisi lagu.');
        }

        //forward req ke function tfidfSearch()
        $results = [];
        if ($query) {
            $results = $this->tfidfSearch($query, $laguData);
        }

        //return hasil search beserta query pencarian
        return view('cari', [
            'results' => $results,
            'query' => $query,
        ]);
    }


    private function tfidfSearch($query, $laguData) {
        $tokenize = function($text) { //tokenisasi
            $text = strtolower($text); //lowercase 
            $text = preg_replace('/[^a-z0-9\s]/', '', $text); //hapus karakter selain huruf
            return preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY); //split string menjadi array berdasarkan spasi
        };

        //tokenisasi lagu dan disimpan di arrat docsToken
        $docsTokens = [];
        foreach ($laguData as $lagu) {
            $docsTokens[] = $tokenize($lagu['lirik']);
        }
        $queryTokens = $tokenize($query); //tokenisasi query pencarian

        //crate daftar kata unik
        $vocab = [];
        foreach ($docsTokens as $tokens) {
            $vocab = array_merge($vocab, $tokens);
        }
        $vocab = array_unique(array_merge($vocab, $queryTokens));
        sort($vocab);
        $vocabIndex = array_flip($vocab);

        $N = count($docsTokens); //jumlah dokumen

        //hitung document freq tiap term
        $df = array_fill(0, count($vocab), 0);
        foreach ($vocab as $termIndex => $term) {
            foreach ($docsTokens as $tokens) {
                if (in_array($term, $tokens)) {
                    $df[$termIndex]++;
                }
            }
        }

        //term freq
        $tf = function($term, $tokens) {
            $count = 0;
            foreach ($tokens as $tok) {
                if ($tok === $term) $count++;
            }
            return $count / count($tokens);
        };

        //hitung vektor TF-IDF untuk query pencarian
        $queryVec = [];
        foreach ($vocab as $termIndex => $term) {
            $tfValue = $tf($term, $queryTokens);
            $idfValue = $df[$termIndex] > 0 ? log($N / $df[$termIndex]) : 0;
            $queryVec[$term] = $tfValue * $idfValue;
        }

        //hitung vektor TF-IDF tiap dokumen dan cosine similarity dengan query
        $scores = [];
        foreach ($docsTokens as $docIndex => $tokens) {
            $docVec = [];
            foreach ($vocab as $termIndex => $term) {
                $tfValue = $tf($term, $tokens);
                $idfValue = $df[$termIndex] > 0 ? log($N / $df[$termIndex]) : 0;
                $docVec[$term] = $tfValue * $idfValue;
            }

            //hitung cosine similarity
            $dot = 0;
            $normDoc = 0;
            $normQuery = 0;
            foreach ($vocab as $term) {
                $dot += ($docVec[$term] ?? 0) * ($queryVec[$term] ?? 0);
                $normDoc += pow($docVec[$term] ?? 0, 2);
                $normQuery += pow($queryVec[$term] ?? 0, 2);
            }
            $normDoc = sqrt($normDoc);
            $normQuery = sqrt($normQuery);

            $cosSim = ($normDoc && $normQuery) ? ($dot / ($normDoc * $normQuery)) : 0;
            $scores[$docIndex] = $cosSim;
        }

        //sort dokumen berdasar similarity tertinggi
        arsort($scores);

        //return array lagu dengan skor > 0
        $results = [];
        foreach ($scores as $docIndex => $score) {
            if ($score > 0) {
                $lagu = $laguData[$docIndex];
                $lagu['score'] = $score;
                $results[] = $lagu;
            }
        }

        return $results;
    }
}
