<?php

namespace App\Http\Controllers\dokter;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create($id)
    {
        $obats = Obat::all();
        return view('dokter.periksa-pasien.create', compact('obats', 'id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_daftar_poli' => 'required|exists:daftar_poli,id',
            'obat_json' => 'required',
            'catatan' => 'nullable|string',
            'biaya_periksa' => 'required|integer',
        ]);

        $obatIds = json_decode($request->obat_json, true);

        DB::beginTransaction();

        try {
            // 1. CEK STOK OBAT
            foreach ($obatIds as $idObat) {
                $obat = Obat::findOrFail($idObat);

                if ($obat->stok <= 0) {
                    return back()
                        ->with('message', "Stok obat {$obat->nama_obat} habis")
                        ->with('type', 'danger');
                }
            }

            // 2. SIMPAN PERIKSA
            $periksa = Periksa::create([
                'id_daftar_poli' => $request->id_daftar_poli,
                'tgl_periksa' => now(),
                'catatan' => $request->catatan,
                'biaya_periksa' => $request->biaya_periksa + 150000,
            ]);

            // 3. SIMPAN DETAIL + KURANGI STOK
            foreach ($obatIds as $idObat) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $idObat,
                ]);

                Obat::where('id', $idObat)->decrement('stok', 1);
            }

            DB::commit();

            return redirect()->route('periksa-pasien.index')
                ->with('success', 'Data periksa berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->with('message', 'Terjadi kesalahan saat menyimpan periksa')
                ->with('type', 'danger');
        }
    }

}
