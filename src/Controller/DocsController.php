<?php

namespace HarmBandstra\SwaggerUiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class DocsController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        return $this->forward(
            'HBSwaggerUiBundle:Docs:redirect',
            ['fileName' => $this->getParameter('hb_swagger_ui.default_file')]
        );
    }

    /**
     * @param Request $request
     * @param string $fileName
     *
     * @return RedirectResponse
     */
    public function redirectAction(Request $request, $fileName)
    {
        $swaggerUiRoute = sprintf('%s/bundles/hbswaggerui/swagger-ui/index.html', $request->getSchemeAndHttpHost());
        $swaggerFileRoute = $this->get('router')->generate('hb_swagger_ui_swagger_file', ['fileName' => $fileName]);

        return $this->redirect(
            sprintf('%s?url=%s%s', $swaggerUiRoute, $request->getSchemeAndHttpHost(), $swaggerFileRoute)
        );
    }

    /**
     * @param string $fileName
     *
     * @return JsonResponse
     */
    public function swaggerFileAction($fileName)
    {
        $filePath = $this->getFilePath($fileName);
        $fileContents = file_get_contents($filePath);

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === 'yml' || $extension === 'yaml') {
            $fileContents = Yaml::parse(file_get_contents($filePath));

            return new JsonResponse($fileContents);
        }

        return new JsonResponse($fileContents, Response::HTTP_OK, [], true);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function getFilePath($fileName)
    {
        $filePath = realpath($this->getParameter('hb_swagger_ui.directory') . DIRECTORY_SEPARATOR . pathinfo($fileName, PATHINFO_BASENAME));
        if (!is_file($filePath)) {
            throw new FileNotFoundException(sprintf('File [%s] not found.', $filePath));
        }

        return $filePath;
    }
}
