<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'El título no puede estar vacío')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'El título debe tener al menos {{ limit }} caracteres',
        maxMessage: 'El título no puede exceder {{ limit }} caracteres'
    )]
    private ?string $titulo = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'childTasks')]
    private ?self $parentTask = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parentTask', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $childTasks;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'tasks', cascade: ['persist', 'remove'])]
    private Collection $tags;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?Usuarios $usuario = null;

    /**
     * @var Collection<int, File>
     */
    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'task')]
    private Collection $files;

    public function __construct()
    {
        $this->childTasks = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getParentTask(): ?self
    {
        return $this->parentTask;
    }

    public function setParentTask(?self $parentTask): static
    {
        $this->parentTask = $parentTask;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildTasks(): Collection
    {
        return $this->childTasks;
    }

    public function addChildTask(self $task): static
    {
        if (!$this->childTasks->contains($task)) {
            $this->childTasks->add($task);
            $task->setParentTask($this);
        }

        return $this;
    }

    public function removeChildTask(self $task): static
    {
        if ($this->childTasks->removeElement($task)) {
            if ($task->getParentTask() === $this) {
                $task->setParentTask(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getUsuario(): ?Usuarios
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuarios $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): static
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setTask($this);
        }

        return $this;
    }

    public function removeFile(File $file): static
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getTask() === $this) {
                $file->setTask(null);
            }
        }

        return $this;
    }
}
