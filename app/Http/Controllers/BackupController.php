<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller {

	public function __construct() {
		setlocale(LC_TIME, 'de_DE', 'deu_deu');
	}

	public function start() {
		$dirs = array_filter(glob(config('backup.path') . '/*'), 'is_dir');

		return view('backup.start', [
			'dirs' => $dirs,
		]);
	}

	public function browse($backup, $categoryId = 0) {

		// Get backup sub-categories dependend on parent-category
		$categories = $this->getCategories($categoryId);

		// Get courses dependend on current category
		$courses = $this->getCourses($categoryId);

		// Get csv-file of backup directory
		$csvFile = $this->getCsv($backup);
		// Only for testing
		// $csv = base_path() . '/shared/stat';

		if (!$csvFile) {
			// No BackupBrowser Directory. Show file browser.
			// Show view directorybrowser
			return view('backup.directorybrowser', [
				'backup' => $backup,
				'dirs' => $this->getServerDirectories(),
				'hideSearch' => TRUE,
			]);
		}

		$fileSystemData = $this->getArrayfromCsv($csvFile);

		// Merge course data of database and filesystem
		$courseData = $this->mergeCourseData($courses, $fileSystemData);

		$dirs = $this->getServerDirectories();

		$breadcrumbs = $this->getBreadcrumbs($backup, $categoryId);
		// dd($breadcrumbs);

		return view('backup.browse', [
			'dirs' => $this->getServerDirectories(),
			'backup' => $backup,
			'categories' => $categories,
			'courseData' => $courseData,
			'breadcrumbs' => $breadcrumbs,
		]);
	}

	public function isBackupBrowserDir($backupDir) {

		$files = scandir($backupDir);

		foreach ($files as $file) {
			if ($file === '.' || $file === '..' || $file === config('backup.cachefile') || $file === config('backup.indexfile')) {
				continue;
			}
			try {
				if (!is_numeric(explode('-', $file)[3]) || pathinfo($file, PATHINFO_EXTENSION) !== config('backup.file_extension')) {
					return false;
				}
			} catch (Exception $e) {
				return false;
			}
		}
		return true;
	}

	protected function getCsv($backup) {

		$backupDir = config('backup.path') . '/' . $backup;
		$cachefile = $backupDir . '/' . config('backup.cachefile');

		if (file_exists($cachefile)) {
			// This is backup-dir. Test if fileage is ok
			if ((time() - filemtime($cachefile)) > (int) config('backup.time_filesystem_refresh')) {
				// File too old, update
				$this->updateCsv($cachefile, $backupDir);
			}
		} else {
			// Cache-file does not exist. If is backup-dir, create it
			if ($this->isBackupBrowserDir($backupDir)) {
				$this->createIndexFile($backupDir);
				$this->updateCsv($cachefile, $backupDir);
			} else {
				// No backup directory, return false.
				return false;
			}

		}
		return $cachefile;
	}

	protected function createIndexFile($dir) {
		file_put_contents($dir . '/' . config('backup.indexfile'), '');
	}

	protected function updateCsv($cachefile, $backupDir) {
		file_put_contents($cachefile, shell_exec('stat -c "%n,%s,%y" ' . $backupDir . '/*.' . config('backup.file_extension')));
	}

	public function search(Request $request, $backup) {

		$search = trim(request('search'));
		$type = request('type');

		if ($search) {
			if ($type === config('backup.select_coursename')) {

				$courses = DB::table('mdl_course')
					->join('mdl_context', 'mdl_course.id', '=', 'mdl_context.instanceid')
					->join('mdl_role_assignments', 'mdl_context.id', '=', 'mdl_role_assignments.contextid')
					->join('mdl_user', 'mdl_role_assignments.userid', '=', 'mdl_user.id')
					->select('mdl_course.id', 'mdl_course.fullname', 'mdl_course.category', 'mdl_course.visible', 'mdl_course.timecreated', 'mdl_course.timemodified', DB::raw('string_agg(concat(mdl_user.firstname, \' \', mdl_user.lastname), \', \') as name'))
					->where([
						['mdl_context.contextlevel', '=', 50], // 50 = course in Moodle
						['mdl_role_assignments.roleid', '=', 3], // 3 = teacher in Moodle
						['mdl_course.fullname', 'ILIKE', '%' . $search . '%'],
					])
					->groupBy('mdl_course.id')
					->get();

			} else {

				$courses = DB::table('mdl_course')
					->join('mdl_context', 'mdl_course.id', '=', 'mdl_context.instanceid')
					->join('mdl_role_assignments', 'mdl_context.id', '=', 'mdl_role_assignments.contextid')
					->join('mdl_user', 'mdl_role_assignments.userid', '=', 'mdl_user.id')
					->select('mdl_course.id', 'mdl_course.fullname', 'mdl_course.category', 'mdl_course.visible', 'mdl_course.timecreated', 'mdl_course.timemodified', DB::raw('string_agg(concat(mdl_user.firstname, \' \', mdl_user.lastname), \', \') as name'))
					->where([
						['mdl_context.contextlevel', '=', 50], // 50 = course in Moodle
						['mdl_role_assignments.roleid', '=', 3], // 3 = teacher in Moodle
						[DB::raw('concat(mdl_user.firstname, \' \', mdl_user.lastname)'), 'ILIKE', '%' . $search . '%'],
					])
					->groupBy('mdl_course.id')
					->get();
			}

		}

		$fileSystemData = $this->getArrayfromCsv($this->getCsv($backup));

		$courseData = $this->mergeCourseData($courses, $fileSystemData);

		return view('backup.search', [
			'courseData' => $courseData,
			'dirs' => $this->getServerDirectories(),
			'backup' => $backup,
		]);
	}

	protected function getServerDirectories() {
		return array_filter(glob(config('backup.path') . '/*'), 'is_dir');
	}

	protected function getBreadcrumbs($backup, $categoryId) {
		$arrResult[] = ['name' => $backup, 'link' => '/' . config('backup.apache_dir') . "/browse/$backup"];

		if ((int) $categoryId > 0) {
			$currentCategory = DB::table('mdl_course_categories')
				->select('mdl_course_categories.id', 'mdl_course_categories.name', 'mdl_course_categories.depth', 'mdl_course_categories.path')
				->where('mdl_course_categories.id', '=', $categoryId)
				->first();

			$arrCategories = explode('/', substr($currentCategory->path, 1));
			foreach ($arrCategories as $key => $category) {
				$nextCategory = DB::table('mdl_course_categories')
					->select('mdl_course_categories.id', 'mdl_course_categories.name')
					->where('mdl_course_categories.id', '=', $category)
					->first();

				$arrResult[] = ['name' => $nextCategory->name, 'link' => '/' . config('backup.apache_dir') . "/browse/$backup/$category"];
			}
		}
		return $arrResult;
	}

	protected function mergeCourseData($courses, $fileSystemData) {
		$arrResult = [];
		$arrTmp = [];
		foreach ($courses as $course) {
			foreach ($fileSystemData as $fsd) {
				if ((int) $fsd['courseid'] === $course->id) {
					$arrTmp['id'] = $fsd['courseid'];
					$arrTmp['filename'][] = $fsd['filename'];
					$arrTmp['filesize'] = $fsd['filesize'];
					$arrTmp['formateddate'] = $fsd['date'];
					$arrTmp['teachername'] = $course->name;
					$arrTmp['name'] = $course->fullname;
					$arrTmp['visible'] = $course->visible;
					$arrResult[] = $arrTmp;
				}
			}
			unset($arrTmp['filename']);
		}
		return $arrResult;
	}

	protected function getCategories($parentId) {
		return DB::table('mdl_course_categories')
			->select('mdl_course_categories.id', 'mdl_course_categories.name', 'mdl_course_categories.visible', 'mdl_course_categories.timemodified')
			->where('mdl_course_categories.parent', '=', $parentId)
			->orderBy('mdl_course_categories.sortorder', 'ASC')
			->get();
	}

	protected function getCourses($categoryId) {
		return DB::table('mdl_course')
			->join('mdl_context', 'mdl_course.id', '=', 'mdl_context.instanceid')
			->join('mdl_role_assignments', 'mdl_context.id', '=', 'mdl_role_assignments.contextid')
			->join('mdl_user', 'mdl_role_assignments.userid', '=', 'mdl_user.id')
			->select('mdl_course.id', 'mdl_course.fullname', 'mdl_course.category', 'mdl_course.visible', 'mdl_course.timecreated', 'mdl_course.timemodified', DB::raw('string_agg(concat(mdl_user.firstname, \' \', mdl_user.lastname), \', \') as name'))
			->where([
				['mdl_context.contextlevel', '=', 50], // 50 = course in Moodle
				['mdl_role_assignments.roleid', '=', 3], // 3 = teacher in Moodle
				['mdl_course.category', '=', $categoryId],
			])
			->groupBy('mdl_course.id')
			->get();
	}

	protected function getArrayfromCsv($csvfile) {

		$arrCsv = [];
		$arrTmp = [];

		if (file_exists($csvfile)) {
			$file = fopen($csvfile, 'r');
			while (($line = fgetcsv($file)) !== FALSE) {
				//$line is an array of the csv elements
				$arrTmp['filename'] = $line[0];
				$arrTmp['courseid'] = explode('-', $line[0])[3];
				$arrTmp['filesize'] = round($line[1] / 1000000, 1); // Size in MB
				$arrDate = explode(' ', explode('.', $line[2])[0]);
				$date = explode('-', $arrDate[0]);
				$arrTmp['date'] = "$date[2].$date[1].$date[0]";
				$arrCsv[] = $arrTmp;
			}
			fclose($file);
		}
		return $arrCsv;
	}

}
