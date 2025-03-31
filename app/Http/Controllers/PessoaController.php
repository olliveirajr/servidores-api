<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\FotosPessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PessoaController extends Controller
{
    public function index()
    {
        $pessoas = Pessoa::with(['fotos', 'enderecos', 'servidorEfetivo', 'servidorTemporario', 'lotacoes'])
            ->paginate(request('per_page', 10));

        return response()->json($pessoas);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:200',
            'data_nascimento' => 'required|date',
            'sexo' => 'required|string|in:Masculino,Feminino,Outro',
            'data_foto' => 'required|date',
            'foto' => 'required|file|mimes:jpg,png|max:2048'
        ]);

        $pessoa = Pessoa::create($validated);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');

            // Gera hash único do arquivo
            $hash = hash_file('sha256', $file->path());

            // Faz upload para o MinIO
            $bucket = config('filesystems.disks.minio.bucket');
            Storage::disk('minio')->put(
                $hash,
                file_get_contents($file)
            );

            // Cria registro da foto
            $pessoa->fotos()->create([
                'data' => $validated['data_foto'],
                'bucket' => $bucket,
                'hash' => $hash
            ]);
        }

        return response()->json($pessoa->load('fotos'), 201);
    }

    public function show(Pessoa $pessoa)
    {
        $pessoa->load(['fotos', 'enderecos', 'servidorEfetivo', 'servidorTemporario', 'lotacoes']);
        return response()->json($pessoa);
    }

    public function update(Request $request, Pessoa $pessoa)
    {
        $validated = $request->validate([
            'nome' => 'sometimes|string|max:200',
            'data_nascimento' => 'sometimes|date',
            'sexo' => 'sometimes|string|in:Masculino,Feminino,Outro',
            'mae' => 'nullable|string|max:200',
            'pai' => 'nullable|string|max:200'
        ]);

        $pessoa->update($validated);
        return response()->json($pessoa);
    }

    public function destroy(Pessoa $pessoa)
    {
        if ($pessoa->servidorEfetivo()->exists() ||
            $pessoa->servidorTemporario()->exists() ||
            $pessoa->lotacoes()->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir pessoa com registros vinculados'
            ], 409);
        }

        $pessoa->delete();
        return response()->json(null, 204);
    }
}
