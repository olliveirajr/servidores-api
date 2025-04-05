<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\FotosPessoa;
use App\Models\ServidorEfetivo;
use App\Models\Endereco;
use App\Models\PessoaEndereco;
use App\Models\Cidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ServidorEfetivoController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        // Define o número de itens por página (padrão: 10)
        $perPage = $request->get('per_page', 10);

        // Recupera os servidores efetivos com paginação
        $servidores = ServidorEfetivo::with(['pessoa.fotos'])
            ->paginate($perPage);

        // Adiciona links temporários para as imagens
        $servidores->getCollection()->transform(function ($servidor) {
            $servidor->pessoa->fotos->transform(function ($foto) {
                if ($foto->hash) {
                    // Gera o link temporário para a imagem
                    $foto->url = Storage::disk('minio')->temporaryUrl(
                        $foto->hash,
                        now()->addMinutes(5) // Expira em 5 minutos
                    );
                }
                return $foto;
            });
            return $servidor;
        });

        return response()->json($servidores, 200);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:200',
                'data_nascimento' => 'required|date',
                'sexo' => 'required|string|in:Masculino,Feminino',
                'mae' => 'required|string|max:200',
                'pai' => 'nullable|string|max:200',
                'tipo_logradouro' => 'required|string|max:50',
                'logradouro' => 'required|string|max:200',
                'numero' => 'required|integer',
                'bairro' => 'required|string|max:100',
                'cidade' => 'required|string|max:200',
                'uf' => 'required|string|max:2',
                'matricula' => 'required|string|max:20|unique:servidores_efetivos,matricula',
                'foto' => 'sometimes|required|file|mimes:jpg,png|max:2048',
                'data_foto' => 'sometimes|required|date',
            ]);

            if (ServidorEfetivo::where('matricula', $validated['matricula'])->exists()) {
                return response()->json(['error' => 'Matrícula já cadastrada.'], 400);
            }

            $cidade = Cidade::firstOrCreate(
                ['nome' => $validated['cidade'], 'uf' => $validated['uf']]
            );

            $endereco = Endereco::create([
                'tipo_logradouro' => $validated['tipo_logradouro'],
                'logradouro' => $validated['logradouro'],
                'numero' => $validated['numero'],
                'bairro' => $validated['bairro'],
                'cidade_id' => $cidade->id,
            ]);

            $pessoa = Pessoa::create([
                'nome' => $validated['nome'],
                'data_nascimento' => $validated['data_nascimento'],
                'sexo' => $validated['sexo'],
                'mae' => $validated['mae'],
                'pai' => $validated['pai'] ?? null,
            ]);

            // 🚨 Garantir que o upload seja feito com sucesso antes de continuar
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');

                try {
                    $hash = hash_file('sha256', $file->path());
                    $bucket = config('filesystems.disks.minio.bucket');

                    Storage::disk('minio')->put($hash, file_get_contents($file));

                    $pessoa->fotos()->create([
                        'data' => $validated['data_foto'],
                        'bucket' => $bucket,
                        'hash' => $hash
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack(); // Reverte tudo se o upload falhar
                    return response()->json([
                        'error' => 'Erro ao fazer upload da imagem.',
                        'details' => $e->getMessage(),
                    ], 500);
                }
            }

            PessoaEndereco::create([
                'pessoa_id' => $pessoa->id,
                'endereco_id' => $endereco->id,
            ]);

            ServidorEfetivo::create([
                'pessoa_id' => $pessoa->id,
                'matricula' => $validated['matricula'],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Servidor efetivo criado com sucesso.',
                'servidor_efetivo' => $pessoa->load(['fotos', 'enderecos.cidade']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Erro ao criar servidor efetivo', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Ocorreu um erro ao criar o servidor efetivo.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();

        try {
            // Validação dos dados
            $validated = $request->validate([
                'nome' => 'sometimes|required|string|max:200',
                'data_nascimento' => 'sometimes|required|date',
                'sexo' => 'sometimes|required|string|in:Masculino,Feminino',
                'mae' => 'sometimes|required|string|max:200',
                'pai' => 'nullable|string|max:200',
                'tipo_logradouro' => 'sometimes|required|string|max:50',
                'logradouro' => 'sometimes|required|string|max:200',
                'numero' => 'sometimes|required|integer',
                'bairro' => 'sometimes|required|string|max:100',
                'cidade' => 'sometimes|required|string|max:200',
                'uf' => 'sometimes|required|string|max:2',
                'matricula' => 'sometimes|required|string|max:20|unique:servidores_efetivos,matricula,' . $id . ',pessoa_id',
                'foto' => 'sometimes|required|string',
                'data_foto' => 'sometimes|required|date',
            ]);

            // Localiza o servidor efetivo pelo ID
            $servidorEfetivo = ServidorEfetivo::findOrFail($id);

            // Atualiza os dados da pessoa associada
            $pessoa = $servidorEfetivo->pessoa;
            if ($pessoa) {
                $pessoa->update($validated);
            }

            // Atualiza o endereço associado
            if ($request->hasAny(['tipo_logradouro', 'logradouro', 'numero', 'bairro', 'cidade', 'uf'])) {
                $endereco = $pessoa->enderecos()->first();
                if ($endereco) {
                    // Atualiza ou cria a cidade, se necessário
                    if ($request->hasAny(['cidade', 'uf'])) {
                        $cidade = Cidade::firstOrCreate(
                            ['nome' => $validated['cidade'], 'uf' => $validated['uf']]
                        );
                        $endereco->cidade_id = $cidade->id;
                    }

                    $endereco->update($validated);
                }
            }

            // Atualiza a matrícula do servidor efetivo
            if ($request->has('matricula')) {
                $servidorEfetivo->update(['matricula' => $validated['matricula']]);
            }

            // Atualiza a foto, se fornecida
            if ($request->has('foto')) {
                // Decodifica a string base64
                $fotoBase64 = $request->input('foto');
                $fotoBinaria = base64_decode($fotoBase64);

                // Remove a foto antiga, se existir
                $fotoAntiga = $pessoa->fotos()->first();
                if ($fotoAntiga) {
                    Storage::disk('minio')->delete($fotoAntiga->hash);
                    $fotoAntiga->delete();
                }

                // Gera hash único do arquivo
                $hash = hash('sha256', $fotoBinaria);

                // Faz upload para o MinIO
                $bucket = config('filesystems.disks.minio.bucket');
                Storage::disk('minio')->put($hash, $fotoBinaria);

                // Cria registro da nova foto
                $pessoa->fotos()->create([
                    'data' => $validated['data_foto'] ?? now(),
                    'bucket' => $bucket,
                    'hash' => $hash,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Servidor efetivo atualizado com sucesso.',
                'servidor_efetivo' => $servidorEfetivo->load(['pessoa', 'pessoa.fotos', 'pessoa.enderecos.cidade']),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao atualizar servidor efetivo: ' . $e->getMessage());
            return response()->json([
                'error' => 'Ocorreu um erro ao atualizar o servidor efetivo.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();

        try {
            // Localiza o servidor efetivo pelo ID
            $servidorEfetivo = ServidorEfetivo::findOrFail($id);

            // Obtém a pessoa associada ao servidor efetivo
            $pessoa = $servidorEfetivo->pessoa;

            if ($pessoa) {
                // Apaga as fotos associadas à pessoa
                foreach ($pessoa->fotos as $foto) {
                    if ($foto && $foto->hash) {
                        // Remove o arquivo do MinIO
                        Storage::disk('minio')->delete($foto->hash);

                        // Remove o registro da foto
                        $foto->delete();
                    }
                }

                // Apaga os endereços associados à pessoa
                foreach ($pessoa->enderecos as $endereco) {
                    if ($endereco) {
                        // Remove o vínculo entre pessoa e endereço
                        PessoaEndereco::where('pessoa_id', $pessoa->id)
                            ->where('endereco_id', $endereco->id)
                            ->delete();

                        // Armazena a cidade associada ao endereço
                        $cidade = $endereco->cidade;

                        // Remove o endereço
                        $endereco->delete();

                        // Verifica se a cidade não está sendo usada por outros endereços
                        if ($cidade && $cidade->enderecos()->count() === 0) {
                            $cidade->delete();
                        }
                    }
                }

                // Apaga a pessoa associada
                $pessoa->delete();
            }

            // Apaga o servidor efetivo
            $servidorEfetivo->delete();

            DB::commit();

            return response()->json([
                'message' => 'Servidor efetivo e todos os relacionamentos foram apagados com sucesso.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Ocorreu um erro ao apagar o servidor efetivo.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
