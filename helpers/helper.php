<?php

if (!function_exists('uploader')) {
    /**
     * @param $file
     * @param string $folder
     * @param array $validation
     * @return mixed
     */
    function uploader($file, string $folder = "", array $validation = []): string
    {
        $request = request();
        $isFile = $file instanceof \Illuminate\Http\UploadedFile;
        // remove any / char form var
        $path = rtrim($folder, '/');
        // validate Image
        if (!$isFile) {
            if (empty($validation)) $request->validate([$file => ['required', 'image', 'mimes:jpeg,jpg,png']]);
            else $request->validate([$file => $validation]);
        }

        $image = $isFile ? $file : $request->file($file);
        $filename = uniqid(config('app.name','UPLOADER'),false). time() . '.' . $image->getClientOriginalExtension();
        $image->storeAs($path, $filename);
        return str_replace('//', '/', 'uploads/' . $path . '/' . $filename);
    }
}
