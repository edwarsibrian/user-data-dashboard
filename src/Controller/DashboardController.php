<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;

final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {
    }

    #[Route('/', name: 'app_home')]
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(Request $request): Response
    {
        $backendBaseUrl = $_ENV['BACKEND_BASE_URL'];

        if (!$backendBaseUrl) {
            throw new \RuntimeException('BACKEND_BASE_URL is not configured.');
        }

        $processesResponse = $this->httpClient->request('GET', $backendBaseUrl . '/api/processes');
        $processesData = $processesResponse->toArray();

        $processes = $processesData['data'] ?? [];
        $summary = [];

        $selectedProcessId = $request->query->get('processId');

        if ($selectedProcessId) {
            $selectedProcess = array_values(array_filter(
                $processes,
                fn ($process) => (string) $process['id'] === (string) $selectedProcessId
            ))[0] ?? null;
        } else {
            $selectedProcess = $processes[0] ?? null;
        }
        
        if ($selectedProcess !== null) {
            $summaryResponse = $this->httpClient->request(
                'GET',
                $backendBaseUrl . '/api/processes/' . $selectedProcess['id'] . '/summary'
            );

            $summaryData = $summaryResponse->toArray();
            $summary = $summaryData['data'] ?? [];
        }

        return $this->render('dashboard/index.html.twig', [
            'processes' => $processes,
            'selectedProcess' => $selectedProcess,
            'summary' => $summary,
        ]);
    }
}