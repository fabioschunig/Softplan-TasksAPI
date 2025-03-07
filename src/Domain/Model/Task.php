<?php

namespace SoftplanTasksApi\Domain\Model;

class Task
{
    public readonly int $id;
    public readonly string $descricao;
    public readonly \DateTime $referencia;
    public readonly \DateTime $inicio;
    public readonly \DateTime $fim;
    public readonly string $observacao;
    public readonly string $origem;

    public function __construct(
        int $id,
        string $descricao,
        \DateTime $referencia,
        \DateTime $inicio,
        \DateTime $fim,
        string $observacao,
        string $origem
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
