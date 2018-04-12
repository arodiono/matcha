<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Models\Photo;
use App\Models\Rating;
use App\Models\User;
use App\Models\Tag;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

/**
 * Class PhotoController
 * @package App\Controllers
 */
class PhotoController extends Controller
{
    public function setProfilePhoto(Request $request, Response $response)
    {

    }
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function upload(Request $request, Response $response): Response
    {
        $files = $request->getUploadedFiles();
        $user = Auth::user();
        $dir = $this->uploads . DIRECTORY_SEPARATOR . $user->username;
        $profilePhoto = $request->getParsedBody()["profile-photo"];

        foreach ($files['files'] as $file) {
            if ($file->getError() === UPLOAD_ERR_OK) {
                $filename = $this->moveUploadedFile($dir, $file);
                $id = Photo::create([
                   'photo' => $filename,
                   'user_id' => $user->id
                ]);
                if ($file->getClientFilename() == $profilePhoto) {
                    $this->auth->user()->setPhoto($id->photo);
                }
            }
        }
        Rating::setRating(Auth::user()->id);
        return $response->withStatus(200);
    }

    private function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        if (!file_exists($this->uploads)) {
            mkdir($this->uploads, 0755);
        }
        if (!file_exists($directory)) {
            mkdir($directory, 0755);
        }
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }
}