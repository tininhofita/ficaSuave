<?php

class CategoriaModel
{
    private $conn;

    public function __construct()
    {
        require_once BASE_PATH . '/app/config/db_config.php';
        $this->conn = getDatabase();
    }

    public function listarComSubcategorias($idUsuario)
    {
        $sql = "SELECT * FROM categorias 
                WHERE id_usuario = ? OR categoria_padrao = TRUE AND ativa = TRUE
                ORDER BY nome_categoria";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $categorias = [];
        while ($cat = $result->fetch_assoc()) {
            $cat['subcategorias'] = $this->buscarSubcategorias($cat['id_categoria'], $idUsuario);
            $categorias[] = $cat;
        }

        $stmt->close();
        return $categorias;
    }

    public function buscarSubcategorias($idCategoria, $idUsuario)
    {
        $sql = "SELECT * FROM subcategorias 
                WHERE id_categoria = ? AND (id_usuario = ? OR subcategoria_padrao = TRUE) AND ativa = TRUE";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $idCategoria, $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $subcategorias = [];
        while ($row = $result->fetch_assoc()) {
            $subcategorias[] = $row;
        }

        $stmt->close();
        return $subcategorias;
    }

    public function buscarPorId($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM categorias WHERE id_categoria = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    public function excluir($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM categorias WHERE id_categoria = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    public function desativar($id)
    {
        $stmt = $this->conn->prepare("UPDATE categorias SET ativa = FALSE WHERE id_categoria = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    public function listarCategorias($idUsuario)
    {
        $sql = "SELECT * FROM categorias 
            WHERE (id_usuario = ? OR categoria_padrao = TRUE) AND ativa = TRUE
            ORDER BY nome_categoria";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }

        $stmt->close();
        return $categorias;
    }

    public function listarSubcategorias($idUsuario)
    {
        $sql = "SELECT * FROM subcategorias 
            WHERE (id_usuario = ? OR subcategoria_padrao = TRUE) AND ativa = TRUE
            ORDER BY nome_subcategoria";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $subcategorias = [];
        while ($row = $result->fetch_assoc()) {
            $subcategorias[] = $row;
        }

        $stmt->close();
        return $subcategorias;
    }
}
