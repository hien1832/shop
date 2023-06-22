<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Order;
use App\Form\ProductFormType;
use App\Form\OrderFormType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ShopController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/shop', name: 'app_shop')]
    public function index(): Response
    {
        $repository = $this->em->getRepository(Product::class);
        $products = $repository->findAll();
        return $this->render('shop/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/profile', name: 'app_shop_profile')]
    public function profile(UserInterface $user, UserPasswordHasherInterface $userPasswordHasher, Request $request, EntityManagerInterface $em): Response
    {
        $username = $user->getUserIdentifier();
        $repository = $this->em->getRepository(User::class);
        $profile = $repository->findOneBy(['username' => $username]);
        $form = $this->createForm(RegistrationFormType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $profile->setPassword(
                $userPasswordHasher->hashPassword(
                    $profile,
                    $form->get('plainPassword')->getData()
                )
            );
            $profile->setUsername($form->get('username')->getData());
            $profile->setName($form->get('name')->getData());
            $profile->setDob($form->get('dob')->getData());
            $profile->setPhone($form->get('phone')->getData());
            $profile->setEmail($form->get('email')->getData());
            $em->persist($user);
            $em->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_shop');
        }
        return $this->render('shop/profile.html.twig', [
            'profile' => $profile,
            'form' => $form->createView()
        ]);
    }

    #[Route('/products', name: 'app_shop_products')]
    public function product(UserInterface $user): Response
    {
        $username = $user->getUserIdentifier();
        $repository = $this->em->getRepository(User::class);
        $profile = $repository->findOneBy(['username' => $username]);
        $products = $profile->getSellings();

        return $this->render('shop/products.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/create', name: 'app_shop_create')]
    public function create(Request $request, EntityManagerInterface $em, UserInterface $user): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductFormType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newProduct = $form->getData();
            $newProduct->setSeller($user);
            $img_path = $form->get('img_path')->getData();

            if ($img_path) {
                $newFileName = uniqid() . '.' . $img_path->guessExtension();
                try {
                    $img_path->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (Exception $e) {
                    return new Response($e->getMessage());
                }
                $newProduct->setImgPath('/uploads/' . $newFileName);
            }
            $em->persist($newProduct);
            $em->flush($newProduct);
            return $this->redirectToRoute('app_shop_products');
        }


        return $this->render('shop/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/products/delete/{id}', name: 'app_shop_delete')]
    public function delete($id, EntityManagerInterface $em, UserInterface $user): Response
    {
        $username = $user->getUserIdentifier();
        $repository = $this->em->getRepository(User::class);
        $profile = $repository->findOneBy(['username' => $username]);
        $repository = $this->em->getRepository(Product::class);
        $product = $repository->find($id);
        if ($profile->getSellings()->contains($product)) {
            $this->em->remove($product);
            $this->em->flush();
        }

        return $this->redirectToRoute('app_shop_products');
    }

    #[Route('/products/edit/{id}', name: 'app_shop_edit')]
    public function edit($id, UserInterface $user, Request $request): Response
    {
        $username = $user->getUserIdentifier();
        $repository = $this->em->getRepository(User::class);
        $profile = $repository->findOneBy(['username' => $username]);
        $repository = $this->em->getRepository(Product::class);
        $product = $repository->find($id);
        if ($profile->getSellings()->contains($product)) {
            $form = $this->createForm(ProductFormType::class, $product);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $img_path = $form->get('img_path')->getData();
                if ($img_path) {
                    $newFileName = uniqid() . '.' . $img_path->guessExtension();
                    try {
                        $img_path->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads',
                            $newFileName
                        );
                    } catch (Exception $e) {
                        return new Response($e->getMessage());
                    }
                    $product->setTitle($form->get('title')->getData());
                    $product->setPrice($form->get('price')->getData());
                    $product->setImgPath('/uploads/' . $newFileName);
                    $product->setDescription($form->get('description')->getData());

                    $this->em->persist($product);
                    $this->em->flush($product);
                } else {
                    $product->setTitle($form->get('title')->getData());
                    $product->setPrice($form->get('price')->getData());
                    $product->setDescription($form->get('description')->getData());

                    $this->em->persist($product);
                    $this->em->flush($product);
                }
                return $this->redirectToRoute('app_shop_products');
            }
        } else {
            return $this->redirectToRoute('app_shop_products');
        }
        return $this->render('shop/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    #[Route('/detail/{id}', name: 'app_shop_detail')]
    public function detail($id, Request $request, UserInterface $user): Response
    {
        //product detail controller
        $repository = $this->em->getRepository(Product::class);
        $product = $repository->find($id);

        //order form controller
        $order = new Order();
        $form = $this->createForm(OrderFormType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setQuantity($form->get('quantity')->getData());
            $order->setPhone($form->get('phone')->getData());
            $order->setEmail($form->get('email')->getData());
            $order->setAddress($form->get('address')->getData());
            $order->setNote($form->get('note')->getData());
            $order->setBuyer($user);
            $order->setItem($product);

            $this->em->persist($order);
            $this->em->flush();

            return $this->redirectToRoute('app_shop_order');
        }
        
        return $this->render('shop/detail.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    #[Route('/order', name: 'app_shop_order')]
    public function order(UserInterface $user): Response
    {
        $username = $user->getUserIdentifier();
        $repository = $this->em->getRepository(User::class);
        $profile = $repository->findOneBy(['username' => $username]);
        $orderings = $profile->getOrdering();
        $products = $profile->getSellings();
        return $this->render('shop/order.html.twig',[
            'orderings' => $orderings,
            'products' => $products
        ]);
    }

    #[Route('/admin', name: 'app_shop_admin')]
    public function admin(): Response
    {
        $repository = $this->em->getRepository(User::class);
        $users = $repository->findAll();

        
        return $this->render('shop/admin.html.twig', [
            'users' => $users
        ]);
    }
    #[Route('/admin/delete/{id}', name: 'app_shop_admin_delete')]
    public function adminDelete($id): Response
    {
        $repository = $this->em->getRepository(User::class);
        $user = $repository->find($id);
        $this->em->remove($user);
        $this->em->flush();
        
        return $this->redirectToRoute('app_shop_admin');
    }

}
