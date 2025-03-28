<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExecutionStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'commands',
    ];

    protected $casts = [
        'commands' => 'array',
    ];

    static $CLONE_REPOSITORY = 'Clone Repository';
    static $UNZIP_ZIP_FILES = 'Unzip ZIP Files';
    static $REMOVE_ZIP_FILES = 'Remove ZIP Files';
    static $EXAMINE_FOLDER_STRUCTURE = 'Examine Folder Structure';
    static $ADD_ENV_FILE = 'Add .env File';
    static $REPLACE_PACKAGE_JSON = 'Replace package.json';
    static $COPY_TESTS_FOLDER = "Copy 'tests' Folder";
    static $NPM_INSTALL = 'NPM Install';
    static $NPM_RUN_START = 'NPM Run Start';
    static $NPM_RUN_TESTS = 'NPM Run Tests';
    static $DELETE_TEMP_DIRECTORY = 'Delete Temp Directory';

    public function getCommandsAttribute($value)
    {
        return json_decode($value);
    }

    public function setCommandsAttribute($value)
    {
        $this->attributes['commands'] = json_encode($value);
    }

    public function projectExecutionSteps()
    {
        return $this->hasMany(ProjectExecutionStep::class);
    }
}
