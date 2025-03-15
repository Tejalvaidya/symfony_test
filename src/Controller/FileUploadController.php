<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class FileUploadController extends AbstractController
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }
    
    #[Route('/api/upload', name: 'upload_csv', methods: ['POST'])]
    public function upload(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        // Check if email is provided in request
        $email_data = $request->request->get('email');


        if (!$email_data) {
            return $this->json(['error' => 'Email is required'], Response::HTTP_BAD_REQUEST);
        }

        // Check if user exists and is an ADMIN


        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email_data]);
        if (!$user) {
            return new JsonResponse([
                'message' => 'User not found',
                'email'   => $email_data
            ], 404);
        }


        if (!$user || $user->getRoles()!='admin') {
            return $this->json(['error' => 'Only ADMIN users can upload files'], Response::HTTP_FORBIDDEN);
        }
        

        // Get uploaded file
        $file = $request->files->get('file');

        if (!$file || $file->getError() !== UPLOAD_ERR_OK) 
        {
            return $this->json(['error' => 'Attachment file not found']);

        }
        // Define the upload directory
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';


        $fileExtension = $file->guessExtension();
        $allowedExtensions = ['csv'];

        
        if (!in_array($fileExtension, $allowedExtensions)) {

            return $this->json(['error' => 'Invalid file format. Please upload a valid CSV file']);

        }

     
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
        $newFileName = 'data' . '.' . $fileExtension;
        $filePath = $file->move($uploadDir, $newFileName);


        // Read CSV File
        $handle = fopen($filePath, 'r');
        fgetcsv($handle); 

        $csvEmails = [];
        $usersData = [];

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {

            $data = array_pad($data, 5, '');

            // Assign columns properly using index numbers
            $name     = trim($data[0] ?? '');
            $email    = strtolower(trim($data[1] ?? ''));
            $username = trim($data[2] ?? '');
            $address  = trim($data[3] ?? '');
            $role     = trim($data[4] ?? '');

            if (!empty($email)) {
                if (!isset($csvEmails[$email])) {
                    $csvEmails[$email] = true;
                    $usersData[] = compact('name', 'email', 'username', 'address', 'role');
                }
            }
        }

        fclose($handle);

        $existingEmails = $entityManager->getRepository(User::class)
        ->createQueryBuilder('u')
        ->select('u.email')
        ->where('u.email IN (:emails)')
        ->setParameter('emails', array_keys($csvEmails))
        ->getQuery()
        ->getResult();

        $existingEmails = array_column($existingEmails, 'email');


        // Insert only non-duplicate records
        $inserted = 0;
        $all_user_data = [];

        foreach ($usersData as $data) {
            $user_data = [];

            $user_data['name']     = $data['name'];        // Column 1 - Name
            $user_data['username'] = $data['username'];    // Column 2 - Username
            $user_data['email']    = strtolower($data['email']); // Column 3 - Email
            $user_data['address']  = $data['address'];     // Column 4 - Address
            $user_data['role']     = $data['role'];        // Column 6 - Role

            if (in_array($user_data['email'], $existingEmails)) 
            {
                continue; // Skip existing emails
            }

            $all_user_data[] = $user_data; 

            // Create new user entity
            $user = new User();
            $user->setName($data['name']);
            $user->setEmail($data['email']);
            $user->setUsername($data['username']);
            $user->setAddress($data['address']);
            $user->setRoles($data['role']);

            $entityManager->persist($user);
            $inserted++;

        //   Send Email Notification
         //   $this->sendEmail($mailer, $data['email'], $data['name']);
        }

        $entityManager->flush(); // Save all inserted records

        return $this->json([
            'message' => $inserted > 0 ? 'Data processed successfully' : 'No new users inserted. Duplicate records found.',
            'inserted_records' => $inserted > 0 ? $all_user_data : count($all_user_data),
            'skipped_record' => count($existingEmails),
        ]);
    }

   private function sendUserNotification(MailerInterface $mailer, string $email, string $name)
    {
        $emailMessage = (new Email())
            ->from('tejalkanade@gmail.com')
            ->to('tejalkanade@gmail.com')
            ->subject('Welcome to Our Platform')
            ->text("Hello $name,\n\nYour account has been successfully created.\n\nThank you!");

        $mailer->send($emailMessage);
    }

  
}