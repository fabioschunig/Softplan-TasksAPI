<?php

namespace SoftplanTasksApi\Domain\Model;

class Task
{
    public readonly int $id;
    public readonly string $descricao;
    public readonly \DateTime|null $referencia;
    public readonly \DateTime|null $inicio;
    public readonly \DateTime|null $fim;
    public readonly string|null $observacao;
    public readonly string|null $origem;

    public function __construct(
        int $id,
        string $descricao,
        \DateTime|null $referencia,
        \DateTime|null $inicio,
        \DateTime|null $fim,
        string|null $observacao,
        string|null $origem
    ) {
        $this->id = $id;
        $this->descricao = $descricao;
        $this->referencia = $referencia;
        $this->inicio = $inicio;
        $this->fim = $fim;
        $this->observacao = $observacao;
        $this->origem = $origem;
    }
}
