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
use Tinify\Tinify;
use Tinify\Source;

/**
 * Class PhotoController
 * @package App\Controllers
 */
class PhotoController extends Controller
{
    protected function createThumbs($filename, $dir)
    {
        $source = Source::fromFile($dir . '/'. $filename);
        $resize = $source->resize([
            "method" => "fit",
            "width" => 275,
            "height" => 275
        ]);
        $thumb = $source->resize([
            "method" => "thumb",
            "width" => 200,
            "height" => 200
        ]);
        $resize->toFile($dir . '/'. '275x275_' . $filename);
        $thumb->toFile($dir . '/'. '200x200_' . $filename);
    }

    public function setProfilePhoto(Request $request, Response $response)
    {
        $user = Auth::user();
        $files = $request->getUploadedFiles();
        $dir = $this->uploads . DIRECTORY_SEPARATOR . $user->username;
        $profilePhoto = $request->getParsedBody()["profile-photo"];

        $file = $files['files'];
        if ($file->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($dir, $file);
            $this->createThumbs($filename, $dir);
            $id = Photo::create([
                'photo' => $filename,
                'user_id' => $user->id
            ]);
            $this->auth->user()->setPhoto($id->photo);
        }
        return $response->withRedirect($this->router->pathFor('user.edit'));
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
        $photoCount = Photo::where('user_id', $user->id)->get()->count();
        $dir = $this->uploads . DIRECTORY_SEPARATOR . $user->username;
        $profilePhoto = $request->getParsedBody()["profile-photo"];

        $responseData[] = $user->username;
        foreach ($files['files'] as $file) {
            if ($photoCount >= 5) {
                return $response->withStatus(403);
            }
            if ($file->getError() === UPLOAD_ERR_OK) {
                $filename = $this->moveUploadedFile($dir, $file);
                $this->createThumbs($filename, $dir);
                $id = Photo::create([
                   'photo' => $filename,
                   'user_id' => $user->id
                ]);
                if ($file->getClientFilename() == $profilePhoto) {
                    $this->auth->user()->setPhoto($id->photo);
                }
                $responseData[] = $id;
                $photoCount++;
            }
        }
        Rating::setRating(Auth::user()->id);
        return $response->withJson($responseData);
    }

    public function deletePhoto(Request $request, Response $response, $args): Response
    {
        $photo = Photo::find((int)$args['id']);
        if (!$photo) {
            return $response->withStatus(404);
        }
        if ($photo->user_id != $_SESSION['user']) {
            return $response->withStatus(403);
        }
        $photo->delete();
        unlink($this->uploads . DIRECTORY_SEPARATOR . Auth::user()->username . DIRECTORY_SEPARATOR . $photo->photo);
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