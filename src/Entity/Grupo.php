<?php

namespace App\Entity;

use App\Repository\GrupoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GrupoRepository::class)]
class Grupo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(length: 150)]
    private ?string $estado = null;

    /**
     * @var Collection<int, Usuarios>
     */
    #[ORM\ManyToMany(targetEntity: Usuarios::class, mappedBy: 'grupos')]
    private Collection $usuarios;

    public function __construct()
    {
        $this->usuarios = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * @return Collection<int, Usuarios>
     */
    public function getUsuarios(): Collection
    {
        return $this->usuarios;
    }

    public function addUsuario(Usuarios $usuario): static
    {
        if (!$this->usuarios->contains($usuario)) {
            $this->usuarios->add($usuario);
            $usuario->addGrupo($this);
        }

        return $this;
    }

    public function removeUsuario(Usuarios $usuario): static
    {
        if ($this->usuarios->removeElement($usuario)) {
            $usuario->removeGrupo($this);
        }

        return $this;
    }
}
