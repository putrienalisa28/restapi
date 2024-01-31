<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\AtreanModel;

class AntreanController extends Controller
{
    public function status($kodepoli, $tglpriksa){
        if (!request()->header('x-token') || !request()->header('x-username')) {
            return response()->json([
                'metadata' => [
                    'message' => 'Unauthorized',
                    'code' => 401
                ]
            ], 401);
        }
        try {
            $listPasien = AtreanModel::where('kodepoli', $kodepoli)
                        ->where('tglpriksa', $tglpriksa)
                        ->selectRaw('namapoli,
                            COUNT(*) as totalantrean,
                            SUM(CASE WHEN statusdipanggil = 0 THEN 1 ELSE 0 END) AS sisaantrean')
                        ->selectSub(function ($query) use ($kodepoli, $tglpriksa) {
                            $query->select('nomorantrean')
                                ->from('antriansoal')
                                ->whereColumn('namapoli', 'namapoli')
                                ->where('statusdipanggil', 0)
                                ->where('kodepoli', $kodepoli)
                                ->where('tglpriksa', $tglpriksa)
                                ->orderBy('nomorantrean')
                                ->limit(1);
                        }, 'antreanpanggil')
                        ->groupBy('namapoli')
                        ->get();

            if($listPasien->isEmpty()){
                return response()->json([
                    'response' => '201: Gagal',
                    'message' => 'Tidak ada data ditemukan'
                ], 201);
            } else {
                return response()->json([
                    'response' => $listPasien,
                    'metadata' => [
                        'message' => 'Ok',
                        'code' => 200
                    ]
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => '201: Gagal',
                'message' => $e->getMessage()
            ], 201);
        }
    }

    public function generateToken(Request $request)
    {
        // Memeriksa apakah header x-username dan x-password diberikan
        if (!$request->header('x-username') || !$request->header('x-password')) {
            return response()->json([
                'metadata' => [
                    'message' => 'Unauthorized',
                    'code' => 401
                ]
            ], 401);
        }

        // Anda bisa menambahkan logika validasi username dan password di sini

        // Dummy token untuk contoh
        $token = '1231242353534645645';

        return response()->json([
            'response' => [
                'token' => $token
            ],
            'metadata' => [
                'message' => 'Ok',
                'code' => 200
            ]
        ], 200);
    }

    public function ambilantrean(Request $request)
{
    // Header validation
    if (!$request->header('x-token') || !$request->header('x-username')) {
        return response()->json([
            'metadata' => [
                'message' => 'Unauthorized',
                'code' => 401
            ]
        ], 401);
    }

    try {
        $request->validate([
            'nomorkartu' => 'required|string',
            'nik' => 'required|string',
            'kodepoli' => 'required|string',
            'tglpriksa' => 'required|date',
            'keluhan' => 'required|string',
        ]);
    
        // Simpan data antrean ke dalam database
        $antrean = new AtreanModel();
        $antrean->nomorkartu = $request->nomorkartu;
        $antrean->nik = $request->nik;
        $antrean->kodepoli = $request->kodepoli;
        $antrean->tglpriksa = $request->tglpriksa;
        $antrean->keluhan = $request->keluhan;
        $antrean->save();

        // Periksa apakah antrean berhasil disimpan
        if ($antrean) {
            return response()->json([
                'response' => $antrean,
                'metadata' => [
                    'message' => 'Sukses',
                    'code' => 200
                ]
            ], 200);
        } else {
            return response()->json([
                'metadata' => [
                    'message' => 'Gagal menyimpan antrean.',
                    'code' => 201
                ]
            ], 201);
        }
    } catch (\Exception $e) {
        return response()->json([
            'metadata' => [
                'message' => 'Gagal: ' . $e->getMessage(),
                'code' => 500
            ]
        ], 500);
    }
}


    public function sisapeserta($nomorkartu,$kodepoli,$tglpriksa){
        if (!request()->header('x-token') || !request()->header('x-username')) {
            return response()->json([
                'metadata' => [
                    'message' => 'Unauthorized',
                    'code' => 401
                ]
            ], 401);
        }
        try {
            $listPasien = AtreanModel::where('nomorkartu', $nomorkartu)
                                    ->where('tglpriksa', $tglpriksa)
                                    ->where('kodepoli', $kodepoli)
                                    ->selectSub(function ($query) use ($kodepoli, $tglpriksa) {
                                        $query->select('nomorantrean')
                                            ->from('antriansoal')
                                            ->where('statusdipanggil', 1)
                                            ->where('kodepoli', $kodepoli)
                                            ->where('tglpriksa', $tglpriksa)
                                            ->orderBy('nomorantrean', 'DESC')
                                            ->limit(1);
                                    }, 'nomorantrean')
                                    ->selectRaw('namapoli,
                                        (SELECT COUNT(*) FROM antriansoal WHERE kodepoli = ? AND tglpriksa = ?) as totalantrean,
                                        (SELECT COUNT(*) FROM antriansoal WHERE statusdipanggil = 0 AND kodepoli = ? AND tglpriksa = ?) AS sisaantrean',
                                        [$kodepoli, $tglpriksa, $kodepoli, $tglpriksa])
                                    ->selectSub(function ($query) use ($kodepoli, $tglpriksa) {
                                        $query->select('nomorantrean')
                                            ->from('antriansoal')
                                            ->where('statusdipanggil', 0)
                                            ->where('kodepoli', $kodepoli)
                                            ->where('tglpriksa', $tglpriksa)
                                            ->orderBy('nomorantrean')
                                            ->limit(1);
                                    }, 'antreanpanggil')
                                    ->groupBy('namapoli')
                                    ->get();


            if($listPasien->isEmpty()){
                return response()->json([
                    'response' => '201: Gagal',
                    'message' => 'Tidak ada data ditemukan'
                ], 201);
            } else {
                return response()->json([
                    'response' => $listPasien,
                    'metadata' => [
                        'message' => 'Ok',
                        'code' => 200
                    ]
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => '201: Gagal',
                'message' => $e->getMessage()
            ], 201);
        }
    }

    public function batal(Request $request)
    {
        // Periksa apakah x-token dan x-username tersedia dalam header
        if (!request()->header('x-token') || !request()->header('x-username')) {
            return response()->json([
                'metadata' => [
                    'message' => 'Unauthorized',
                    'code' => 401
                ]
            ], 401);
        }

        // Validasi request
        $request->validate([
            'nomorkartu' => 'required|string',
            'kodepoli' => 'required|string',
            'tglpriksa' => 'required|date',
        ]);

        // Lakukan logika pembatalan antrean
        $antrean = AtreanModel::where([
            'nomorkartu' => $request->nomorkartu,
            'kodepoli' => $request->kodepoli,
            'tglpriksa' => $request->tglpriksa,
        ])->first();
        // echo json_encode($antrean);
        // die;

        // Periksa apakah entri antrean ditemukan
        if ($antrean) {
            $antrean->delete(); // Hapus entri antrean dari database

            // Berikan respons sukses
            return response()->json([
                'metadata' => [
                    'code' => 200,
                    'message' => 'Antrean peserta dengan nomor kartu ' . $request->nomorkartu . ' pada tanggal ' . $request->tglpriksa . ' telah berhasil dibatalkan.'
                ]
            ]);
        } else {
            // Jika entri antrean tidak ditemukan, kirim respons gagal
            return response()->json([
                'metadata' => [
                    'code' => 201,
                    'message' => 'Antrean peserta tidak ditemukan.'
                ]
            ], 404);
        }
    
    }
}