<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware(['is_admin'])->only(['store', 'update', 'destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(request $request)
    {
        $books = Book::filters($request)->get();
        return response()->json(['books' => $books], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'string|required',
                'description' => 'string|required',
                'category' => 'string|required',
                'author' => 'string|required',
                'number_of_pages' => 'integer|required',
                'cover' => 'file|required|mimes:jpg,png|max:10485760',
                'book' => 'file|required|mimes:pdf'
            ]
        );
        if ($validator->fails())
            return response()->json($validator->errors(), 400);
        $data = $validator->validated();
        $name = str_replace(' ', '', $data['title']);
        $bookName = $name . '.pdf';
        $coverName = $name . '.' . $data['cover']->getClientOriginalExtension();
        $bookFolderName = 'Books/' . $data['category'] . '/' . str_replace(' ', '', $data['author']) . '/' . $name;
        $bookPath = $data['book']->storeAs($bookFolderName, $bookName, 'public');
        $bookCoverPath = $data['cover']->storeAs($bookFolderName, $coverName, 'public');
        $book = Book::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'number_of_pages' => $data['number_of_pages'],
            'cover' => $bookCoverPath,
            'path' => $bookPath,
            'author' => $data['author'],
            'category' => $data['category']
        ]);
        return response()->json(['book' => $book], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $book = Book::find($id);
        if ($book)
            return response()->json(['book' => $book], 200);
        else return response()->json(['message' => 'Book Not Exist'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'string',
                'description' => 'string',
                'category' => 'string',
                'author' => 'string',
                'number_of_pages' => 'integer',
                'cover' => 'file|mimes:jpg,png|max:10485760',
                'book' => 'file|mimes:pdf'
            ]
        );
        if ($validator->fails())
            return response()->json($validator->errors(), 400);
        $data = $validator->validated();
        $book = Book::find($id);
        if ($book) {
            $name = str_replace(' ', '', ($data['title'] ?? $book->title));
            $bookName = $name . '.pdf';
            $coverName = $name . '.' . pathinfo($book->cover, PATHINFO_EXTENSION);
            $bookFolderName = 'Books/' . ($data['category'] ?? $book->category) . '/' . str_replace(' ', '', ($data['author'] ?? $book->author)) . '/' . $name;
            Storage::disk('public')->move(pathinfo($book->path, PATHINFO_DIRNAME), $bookFolderName);
            Storage::disk('public')->move($bookFolderName . '/' . basename($book->path), $bookFolderName . '/' . $bookName);
            Storage::disk('public')->move($bookFolderName . '/' . basename($book->cover), $bookFolderName . '/' . $coverName);
            $bookPath = $bookFolderName . '/' . $bookName;
            $bookCoverPath = $bookFolderName . '/' . $coverName;
            if ($request->hasFile('book')) {
                Storage::disk('public')->delete($bookFolderName . '/' . $bookName);
                $bookPath = $data['book']->storeAs($bookFolderName, $bookName, 'public');
            }
            if ($request->hasFile('cover')) {
                Storage::disk('public')->delete($bookFolderName . '/' . $coverName);
                $coverName = $name . '.' . $data['cover']->getClientOriginalExtension();
                $bookCoverPath = $data['cover']->storeAs($bookFolderName, $coverName, 'public');
            }
            $book->update([
                'title' => ($data['title'] ?? $book->title),
                'description' => ($data['description'] ?? $book->description),
                'category' => ($data['category'] ?? $book->category),
                'author' => ($data['author'] ?? $book->author),
                'number_of_pages' => ($data['number_of_pages'] ?? $book->number_of_pages),
                'cover' => $bookCoverPath,
                'path' => $bookPath,
            ]);
            return response()->json(['book' => $book], 200);
        } else return response()->json(['message' => 'Book Not Exist'], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $book = Book::find($id);
        if ($book) {
            Storage::disk('public')->deleteDirectory(pathinfo($book->path, PATHINFO_DIRNAME));
            $book->delete();
            return response()->json(['message' => 'Book Deleted Successfully'], 200);
        } else return response()->json(['message' => 'Book Not Exist'], 404);
    }
    public function download($id)
    {
        $book = Book::find($id);
        if ($book) {
            $bookPath = 'storage/' . $book->path;
            return response()->download($bookPath, $book->name);
        } else return response()->json(['message' => 'Book Not Exist'], 404);
    }
}
