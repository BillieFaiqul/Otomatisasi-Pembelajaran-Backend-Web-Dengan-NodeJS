<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Datatables;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $projects = Project::skip(0)->take(3)->get();
        if ($request->ajax()) {
            $data = DB::table('projects')
                ->select('projects.id', 'projects.title', DB::raw('COUNT(submissions.id) as submission_count'))
                ->leftJoin('submissions', function ($join) use ($user) {
                    $join->on('projects.id', '=', 'submissions.project_id')
                        ->where('submissions.user_id', '=', $user->id);
                })
                ->groupBy('projects.id', 'projects.title');


            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($row) {
                    $title_button = '<a href="/submissions/project/' . $row->id . '" class="underline text-secondary">' . $row->title . '</a>';
                    return $title_button;
                })
                ->addColumn('submission_count', function ($row) {
                    $submission_count = $row->submission_count ?? 0;
                    $text = $submission_count > 0 ? 'Submitted' : 'No Submission';
                    $span_color = $submission_count > 0 ? 'green' : 'gray';
                    $span = '<span class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-xs font-bold leading-none bg-' . $span_color . '-100 text-' . $span_color . '-800">' . ucfirst($text) . '</span>';
                    return $span;
                })
                ->rawColumns(['title', 'submission_count'])
                ->make(true);
        }
        return view('dashboard.index', compact('projects'));
    }
}
