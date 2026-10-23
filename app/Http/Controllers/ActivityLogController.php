<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityLogController extends Controller
{
    /**
     * Menampilkan halaman Audit Trail (DataTables akan memuat data via AJAX).
     * CATATAN: Log aktivitas bersifat permanen dan tidak memiliki fitur hapus / modifikasi.
     */
    public function index(Request $request)
    {
        // Statistik KPI ringkas
        $totalLogs    = ActivityLog::count();
        $totalChanges = ActivityLog::whereIn('action', ['INSERT', 'UPDATE', 'DELETE'])->count();
        $totalViews   = ActivityLog::where('action', 'VIEW')->count();
        $todayLogs    = ActivityLog::whereDate('created_at', Carbon::today())->count();

        // Daftar modul untuk dropdown filter kolom
        $availableModules = ActivityLog::select('module')
            ->distinct()
            ->orderBy('module', 'asc')
            ->pluck('module');

        return view('activity_logs.index', compact(
            'totalLogs',
            'totalChanges',
            'totalViews',
            'todayLogs',
            'availableModules'
        ));
    }

    /**
     * JSON endpoint untuk DataTables server-side processing.
     * Route: GET /log-aktivitas/data
     */
    public function data(Request $request)
    {
        $query = ActivityLog::query();

        $columns = ['created_at', 'admin_name', 'action', 'module', 'description', 'ip_address'];

        // Per-column search
        if ($request->filled('search_tanggal')) {
            $query->where('created_at', 'like', '%' . $request->search_tanggal . '%');
        }
        if ($request->filled('search_admin')) {
            $val = $request->search_admin;
            $query->where(function ($q) use ($val) {
                $q->where('admin_name', 'like', "%{$val}%")
                  ->orWhere('admin_email', 'like', "%{$val}%");
            });
        }
        if ($request->filled('search_action')) {
            $query->where('action', strtoupper($request->search_action));
        }
        if ($request->filled('search_module')) {
            $query->where('module', 'like', '%' . $request->search_module . '%');
        }
        if ($request->filled('search_desc')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search_desc . '%')
                  ->orWhere('method', 'like', '%' . $request->search_desc . '%');
            });
        }
        if ($request->filled('search_ip')) {
            $query->where('ip_address', 'like', '%' . $request->search_ip . '%');
        }

        // Global search (DataTables bawaan)
        $globalSearch = $request->input('search.value', '');
        if (!empty($globalSearch)) {
            $keyword = $globalSearch;
            $query->where(function ($q) use ($keyword) {
                $q->where('admin_name', 'like', "%{$keyword}%")
                  ->orWhere('admin_email', 'like', "%{$keyword}%")
                  ->orWhere('action', 'like', "%{$keyword}%")
                  ->orWhere('module', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhere('ip_address', 'like', "%{$keyword}%");
            });
        }

        $totalFiltered = $query->count();
        $totalAll      = ActivityLog::count();

        // Sorting
        $orderColIdx = (int) $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol    = $columns[$orderColIdx] ?? 'created_at';
        $query->orderBy($orderCol, $orderDir);

        // Pagination
        $start  = max(0, (int) $request->input('start', 0));
        $lengths = [10, 25, 50, 100];
        $length = in_array((int) $request->input('length', 25), $lengths) ? (int) $request->input('length', 25) : 25;
        $rows   = $query->skip($start)->take($length)->get();

        $data = $rows->map(function ($log) {
            $actionLabel = match (strtoupper($log->action)) {
                'INSERT' => '<span class="badge bg-success text-white px-2 py-1 fw-bold" style="font-size:0.74rem;"><i class="bi bi-plus-circle-fill me-1"></i>Tambah</span>',
                'UPDATE' => '<span class="badge bg-warning text-dark px-2 py-1 fw-bold" style="font-size:0.74rem;"><i class="bi bi-pencil-square me-1"></i>Perbarui</span>',
                'DELETE' => '<span class="badge bg-danger text-white px-2 py-1 fw-bold" style="font-size:0.74rem;"><i class="bi bi-trash3-fill me-1"></i>Hapus</span>',
                'VIEW'   => '<span class="badge bg-primary text-white px-2 py-1 fw-bold" style="font-size:0.74rem;"><i class="bi bi-eye-fill me-1"></i>Akses Menu</span>',
                default  => '<span class="badge bg-secondary text-white px-2 py-1">' . e($log->action) . '</span>',
            };

            $initial = strtoupper(substr($log->admin_name ?? 'A', 0, 1));
            $adminCol = '<div class="d-flex align-items-center gap-2">'
                . '<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:28px;height:28px;font-size:0.75rem;flex-shrink:0;">' . e($initial) . '</div>'
                . '<div><div class="fw-bold text-dark" style="font-size:0.85rem;line-height:1.2;">' . e($log->admin_name) . '</div>'
                . '<small class="text-muted" style="font-size:0.72rem;">' . e($log->admin_email ?? 'Pengelola') . '</small></div></div>';

            $urlPath  = parse_url($log->url ?? '', PHP_URL_PATH) ?? '-';
            $descCol  = '<div class="fw-semibold text-dark" style="font-size:0.87rem;line-height:1.35;">' . e($log->description) . '</div>'
                . '<div class="mt-1" style="font-size:0.72rem;">'
                . '<code class="text-secondary bg-light px-1 rounded border">' . e($log->method) . '</code> '
                . '<span class="text-muted text-truncate d-inline-block align-bottom" style="max-width:260px;" title="' . e($log->url) . '">' . e($urlPath) . '</span></div>';

            $ipCol = '<div class="small fw-bold text-secondary"><i class="bi bi-geo-alt-fill text-muted me-1"></i>' . e($log->ip_address ?? '127.0.0.1') . '</div>'
                . '<div class="text-muted text-truncate" style="font-size:0.7rem;max-width:140px;" title="' . e($log->user_agent) . '"><i class="bi bi-laptop me-1"></i>' . e(Str::limit($log->user_agent, 22)) . '</div>';

            $moduleCol = '<span class="badge bg-light text-dark border px-2 py-1 fw-semibold" style="font-size:0.79rem;">' . e($log->module) . '</span>';

            $tgl = $log->created_at
                ? '<div class="fw-bold text-dark" style="font-size:0.87rem;">' . $log->created_at->format('d/m/Y H:i:s') . '</div>'
                  . '<small class="text-muted" style="font-size:0.73rem;">' . $log->created_at->diffForHumans() . '</small>'
                : '-';

            return [
                'created_at'  => $tgl,
                'admin_name'  => $adminCol,
                'action'      => $actionLabel,
                'module'      => $moduleCol,
                'description' => $descCol,
                'ip_address'  => $ipCol,
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $totalAll,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data,
        ]);
    }
}
