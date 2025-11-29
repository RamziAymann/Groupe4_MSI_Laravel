<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $statut = $request->get('statut');

        $query = Client::query();

        if ($statut) {
            $query->where('statut', $statut);
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $clients
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:10',
            'date_naissance' => 'nullable|date',
            'statut' => 'in:actif,inactif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $client = Client::create($request->all());

        // Publier dans la queue
        $this->queueService->publishMessage($client->toArray(), 'create');

        return response()->json([
            'success' => true,
            'message' => 'Client créé avec succès',
            'data' => $client
        ], 201);
    }

    public function show($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }

    public function update(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'string|max:255',
            'prenom' => 'string|max:255',
            'email' => 'email|unique:clients,email,' . $id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:10',
            'date_naissance' => 'nullable|date',
            'statut' => 'in:actif,inactif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $client->update($request->all());

        // Publier dans la queue
        $this->queueService->publishMessage($client->toArray(), 'update');

        return response()->json([
            'success' => true,
            'message' => 'Client mis à jour avec succès',
            'data' => $client
        ]);
    }

    public function destroy($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé'
            ], 404);
        }

        $clientData = $client->toArray();
        $client->delete();

        // Publier dans la queue
        $this->queueService->publishMessage($clientData, 'delete');

        return response()->json([
            'success' => true,
            'message' => 'Client supprimé avec succès'
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Paramètre de recherche requis'
            ], 400);
        }

        $clients = Client::where('nom', 'LIKE', "%{$query}%")
            ->orWhere('prenom', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('telephone', 'LIKE', "%{$query}%")
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $clients
        ]);
    }
}