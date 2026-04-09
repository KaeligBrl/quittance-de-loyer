<?php

namespace App\Controller;

use App\Entity\Tenant;
use App\Entity\TenantFile;
use App\Form\TenantFileType;
use App\Repository\TenantFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/locataires/{id}/documents', name: 'app_tenant_file_')]
class TenantFileController extends AbstractController
{
    public function __construct(
        private string $tenantFilesDirectory,
        private SluggerInterface $slugger,
    ) {}

    #[Route('', name: 'index', methods: ['GET', 'POST'])]
    public function index(Tenant $tenant, Request $request, EntityManagerInterface $em): Response
    {
        $file = new TenantFile();
        $form = $this->createForm(TenantFileType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('file')->getData();
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename  = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            $dir = $this->tenantFilesDirectory . '/' . $tenant->getId();
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $uploadedFile->move($dir, $newFilename);

            $file->setTenant($tenant);
            $file->setOriginalName($uploadedFile->getClientOriginalName());
            $file->setFilename($newFilename);
            $em->persist($file);
            $em->flush();

            $this->addFlash('success', 'Document ajouté avec succès.');
            return $this->redirectToRoute('app_tenant_file_index', ['id' => $tenant->getId()]);
        }

        $filesByCategory = [];
        foreach (TenantFile::CATEGORIES as $key => $label) {
            $filesByCategory[$key] = [
                'label'    => $label,
                'required' => in_array($key, TenantFile::REQUIRED_CATEGORIES),
                'files'    => [],
            ];
        }
        foreach ($tenant->getFiles() as $f) {
            $filesByCategory[$f->getCategory()]['files'][] = $f;
        }

        return $this->render('tenant_file/index.html.twig', [
            'tenant'          => $tenant,
            'filesByCategory' => $filesByCategory,
            'form'            => $form,
        ]);
    }

    #[Route('/{fileId}/download', name: 'download', methods: ['GET'])]
    public function download(Tenant $tenant, int $fileId, TenantFileRepository $repo): Response
    {
        $file = $repo->find($fileId);
        if (!$file || $file->getTenant()->getId() !== $tenant->getId()) {
            throw $this->createNotFoundException();
        }
        $path = $this->tenantFilesDirectory . '/' . $tenant->getId() . '/' . $file->getFilename();
        return (new BinaryFileResponse($path))
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getOriginalName());
    }

    #[Route('/{fileId}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Tenant $tenant, int $fileId, TenantFileRepository $repo, EntityManagerInterface $em, Request $request): Response
    {
        $file = $repo->find($fileId);
        if (!$file || $file->getTenant()->getId() !== $tenant->getId()) {
            throw $this->createNotFoundException();
        }
        if ($this->isCsrfTokenValid('delete_file_' . $fileId, $request->request->get('_token'))) {
            $path = $this->tenantFilesDirectory . '/' . $tenant->getId() . '/' . $file->getFilename();
            if (file_exists($path)) {
                unlink($path);
            }
            $em->remove($file);
            $em->flush();
            $this->addFlash('success', 'Document supprime.');
        }
        return $this->redirectToRoute('app_tenant_file_index', ['id' => $tenant->getId()]);
    }
}
