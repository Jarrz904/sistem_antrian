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
        // Kita prioritaskan antrian yang baru saja diupdate (dipanggil)
        // agar JavaScript mudah mendeteksi perubahan untuk trigger suara
        $data = Queue::with(['layanan', 'loket'])
            ->where('status', 'dipanggil')
            ->whereDate('created_at', Carbon::today())
            ->orderBy('updated_at', 'desc') // Penting: Yang baru dipanggil muncul di atas
            ->get();

        return response()->json($data);
    }
}