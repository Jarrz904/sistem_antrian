<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Support\Carbon;

class DisplayController extends Controller
{
    public function index() {
        return view('public.display');
    }

    public function getDisplayData() {
        // Menggunakan join agar bisa melakukan sorting berdasarkan kolom di tabel lokets
        $data = Queue::with(['layanan', 'loket'])
            ->join('lokets', 'queues.loket_id', '=', 'lokets.id') // Gabungkan dengan tabel loket
            ->select('queues.*') // Pastikan hanya mengambil kolom dari tabel queue
            ->where('queues.status', 'dipanggil')
            ->whereDate('queues.created_at', Carbon::today())
            ->orderBy('lokets.nama_loket', 'asc') // URUTKAN BERDASARKAN NAMA LOKET (Loket 1, 2, dst)
            ->get();

        return response()->json($data);
    }
}