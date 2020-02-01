<?php

namespace SherryLo\Laravel\Admin\Backup;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController
{
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Backup');

            $backup = Backup::getInstance();
            $content->body(view('laravel-admin-backup::backup/index', [
                'backups' => $backup->getExists(),
            ]));
        });
    }

    /**
     * Download a backup zip file.
     *
     * @param Request $request
     *
     * @return StreamedResponse|Response
     */
    public function download(Request $request)
    {
        $disk = Storage::disk($request->get('disk'));
        $file = $request->get('file');
        if ($disk->exists($file)) {
            $filename = basename($file);
            return Storage::download($file, $filename);
        }
        return response('', 404);
    }

    /**
     * Run `backup:run` command.
     *
     * @return JsonResponse
     */
    public function run()
    {
        try {
            ini_set('max_execution_time', 300);

            // start the backup process
            Artisan::call('backup:run');

            $output = Artisan::output();

            return response()->json([
                'status' => true,
                'message' => $output,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete a backup file.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        $disk = Storage::disk($request->get('disk'));
        $file = $request->get('file');

        if ($disk->exists($file)) {
            $disk->delete($file);

            return response()->json([
                'status' => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => trans('admin.delete_failed'),
        ]);
    }
}
