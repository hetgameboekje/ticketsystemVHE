<?php

namespace App\Core;

abstract class CrudController extends Controller
{
    protected string $modelClass;
    protected string $viewDir;
    protected string $routeBase;
    protected string $activeModule;
    protected string $pageTitle;

    public function index(): void
    {
        $this->requireAuth();
        $items = ($this->modelClass)::allWithRelations();
        $items = TableQuery::apply($items, $_GET);

        $this->render("{$this->viewDir}/index", [
            'items' => $items,
            'activeModule' => $this->activeModule,
            'pageTitle' => $this->pageTitle,
            'routeBase' => $this->routeBase,
            'sort' => $_GET['sort'] ?? null,
            'dir' => ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc',
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->render("{$this->viewDir}/create", array_merge($this->formData(), [
            'activeModule' => $this->activeModule,
            'pageTitle' => $this->pageTitle,
            'routeBase' => $this->routeBase,
        ]));
    }

    public function store(): void
    {
        $this->requireAuth();
        $data = $this->validatedData($_POST);
        $id = ($this->modelClass)::create($data);
        $this->redirect("/{$this->routeBase}/{$id}");
    }

    public function show(int $id): void
    {
        $this->requireAuth();
        $item = ($this->modelClass)::findWithRelations($id);

        if ($item === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $this->render("{$this->viewDir}/show", array_merge(['item' => $item], [
            'activeModule' => $this->activeModule,
            'pageTitle' => $this->pageTitle,
            'routeBase' => $this->routeBase,
        ]));
    }

    public function edit(int $id): void
    {
        $this->requireAuth();
        $item = ($this->modelClass)::find($id);

        if ($item === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $this->render("{$this->viewDir}/edit", array_merge(['item' => $item], $this->formData(), [
            'activeModule' => $this->activeModule,
            'pageTitle' => $this->pageTitle,
            'routeBase' => $this->routeBase,
        ]));
    }

    public function update(int $id): void
    {
        $this->requireAuth();
        $data = $this->validatedData($_POST, isUpdate: true);
        ($this->modelClass)::update($id, $data);
        $this->redirect("/{$this->routeBase}/{$id}");
    }

    public function destroy(int $id): void
    {
        $this->requireAuth();
        ($this->modelClass)::delete($id);
        $this->redirect("/{$this->routeBase}");
    }

    protected function formData(): array
    {
        return [];
    }

    abstract protected function validatedData(array $post, bool $isUpdate = false): array;
}
