<?php

namespace MINSAL\IndicadoresBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use MINSAL\IndicadoresBundle\Validator as CustomAssert;
use Doctrine\ORM\EntityManager as Manager;
/**
 * MINSAL\IndicadoresBundle\Entity\OrigenDatos
 *
 * @ORM\Table(name="origen_datos")
 * @ORM\Entity(repositoryClass="MINSAL\IndicadoresBundle\Entity\OrigenDatosRepository")
 * @UniqueEntity(fields="sentenciaSql", message="La sentencia SQL ya fue utilizada")
 */
class OrigenDatos
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $nombre
     *
     * @ORM\Column(name="nombre", type="string", length=100, nullable=false)
     */
    private $nombre;

    /**
     * @var string $descripcion
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @var string $sentenciaSql
     *
     * @ORM\Column(name="sentencia_sql", type="text", nullable=true)
     */
    private $sentenciaSql;

    /**
     * @var Conexiones
     *
     * @ORM\ManyToMany(targetEntity="Conexion", inversedBy="origenes")
     * @ORM\JoinTable(name="origenes_conexiones")
     */
    private $conexiones;

    /**
     * @var string $archivoNombre
     *
     * @ORM\Column(name="archivo_nombre", type="string", length=100, nullable=true)
     */
    protected $archivoNombre;
    /**
     * @Assert\File(
	 *     maxSize = "1024k",
	 *     mimeTypes = {"application/vnd.oasis.opendocument.text","application/vnd.ms-excel","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","text/plain"},
	 *     mimeTypesMessage = "AssertFile.Message")
     */
	 public $file;

    /**
     * @var string $esFusionado
     *
     * @ORM\Column(name="es_fusionado", type="boolean", nullable=true)
     */
    private $esFusionado;

    /**
     * @var string $esPivote
     *
     * @ORM\Column(name="es_pivote", type="boolean", nullable=true)
     */
    private $esPivote;

    /**
     * @var string $esCatalogo
     *
     * @ORM\Column(name="es_catalogo", type="boolean", nullable=true)
     */
    private $esCatalogo;
	
	/**
     * @var string $fechaCorte
     *
     * @ORM\Column(name="fecha_corte", type="integer", nullable=true)
     */
    private $fechaCorte;

    /**
     * @var string $nombreCatalogo
     *
     * @ORM\Column(name="nombre_catalogo", type="string", length=100, nullable=true)
     */
    protected $nombreCatalogo;

    /**
     * @var string $camposFusionados
     *
     * @ORM\Column(name="campos_fusionados", type="text", nullable=true)
     */
    private $camposFusionados;
    
    /**
     * @var int $ventana
     *
     * @ORM\Column(name="ventana", type="integer", nullable=true)
     */
    private $ventana;

    /**
     * @var boolean $actualizacionIncremental
     *
     * @ORM\Column(name="actualizacion_incremental", type="boolean", nullable=true)
     */
    private $actualizacionIncremental;

    /**
     *
     * @var periodicidad
     *
     * @ORM\ManyToOne(targetEntity="Periodos")
     * @ORM\JoinColumn(name="id_periodicidad", referencedColumnName="id")
     * @ORM\OrderBy({"descripcion" = "ASC"})
     **/
    private $periodicidad;

    /**
     * @ORM\OneToMany(targetEntity="ReporteActualizacion", mappedBy="origenDatos")
     * */
    private $reporteActualizaciones;
    
    /**
     * @ORM\ManyToMany(targetEntity="OrigenDatos")
     * @ORM\JoinTable(name="origen_datos_fusiones",
     *      joinColumns={@ORM\JoinColumn(name="id_origen_dato", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="id_origen_dato_fusionado", referencedColumnName="id")}
     *      )
     * */
    private $fusiones;

    /**
     * @ORM\OneToMany(targetEntity="Campo", mappedBy="origenDato")
     */
    private $campos;

    /**
     * @ORM\OneToMany(targetEntity="VariableDato", mappedBy="origenDatos")
     * */
    private $variables;

    public function __construct()
    {
        $this->fusiones = new \Doctrine\Common\Collections\ArrayCollection();
        $this->conexiones = new \Doctrine\Common\Collections\ArrayCollection();
        $this->esCatalogo = false;
		$this->actualizacionIncremental = false;	
    }

    public function getAbsolutePath()
    {
        return null === $this->archivoNombre ? null : $this->getUploadRootDir() . '/' . $this->archivoNombre;
    }

    public function getWebPath()
    {
        return null === $this->archivoNombre ? null : $this->getUploadDir() . '/' . $this->archivoNombre;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
        //return $basepath . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'uploads/origen_datos';
    }

    public function upload($basepath)
    {
        // the file property can be empty if the field is not required
        if (null === $this->file) {
            return;
        }

        if (null === $basepath) {
            return;
        }

        // we use the original file name here but you should
        // sanitize it at least to avoid any security issues
        // move takes the target directory and then the target filename to move to
        $this->file->move($this->getUploadRootDir($basepath), $this->file->getClientOriginalName());

        // set the path property to the filename where you'ved saved the file
        $this->setArchivoNombre($this->file->getClientOriginalName());

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombre
     *
     * @param  string     $nombre
     * @return TablaDatos
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set descripcion
     *
     * @param  string     $descripcion
     * @return TablaDatos
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set archivoNombre
     *
     * @param  string      $archivoNombre
     * @return OrigenDatos
     */
    public function setArchivoNombre($archivoNombre)
    {
        $this->archivoNombre = $archivoNombre;

        return $this;
    }

    /**
     * Get archivoNombre
     *
     * @return string
     */
    public function getArchivoNombre()
    {
        return $this->archivoNombre;
    }

    /**
     * Set sentenciaSql
     *
     * @param  string      $sentenciaSql
     * @return OrigenDatos
     */
    public function setSentenciaSql($sentenciaSql)
    {
        $this->sentenciaSql = $sentenciaSql;

        return $this;
    }

    /**
     * Get sentenciaSql
     *
     * @return string
     */
    public function getSentenciaSql()
    {
        return $this->sentenciaSql;
    }

    public function __toString()
    {
        return $this->nombre ? : '';
    }

    /**
     * Set esFusionado
     *
     * @param  boolean     $esFusionado
     * @return OrigenDatos
     */
    public function setEsFusionado($esFusionado)
    {
        $this->esFusionado = $esFusionado;

        return $this;
    }

    /**
     * Get esFusionado
     *
     * @return boolean
     */
    public function getEsFusionado()
    {
        return $this->esFusionado;
    }

    /**
     * Set camposFusionados
     *
     * @param  string      $camposFusionados
     * @return OrigenDatos
     */
    public function setCamposFusionados($camposFusionados)
    {
        $this->camposFusionados = $camposFusionados;

        return $this;
    }

    /**
     * Get camposFusionados
     *
     * @return string
     */
    public function getCamposFusionados()
    {
        return $this->camposFusionados;
    }
    
    /**
    * Set ventana
     *
     * @param int $ventana
     * @return OrigenDatos
     */
    public function setVentana($ventana) {
        $this->ventana = $ventana;

        return $this;
    }

    /**
     * Get ventana
     *
     * @return int
     */
    public function getVentana() {
        return $this->ventana;
    }

    /**
     * Set actualizacionIncremental
     *
     * @param boolean $actualizacionIncremental
     * @return OrigenDatos
     */
    public function setActualizacionIncremental($actualizacionIncremental) {
        $this->actualizacionIncremental = $actualizacionIncremental;

        return $this;
    }

    /**
     * Get actualizacionIncremental
     *
     * @return boolean
     */
    public function getActualizacionIncremental() {
        return $this->actualizacionIncremental;
    }

    /**
     * Set periodicidad
     *
     * @param \MINSAL\IndicadoresBundle\Entity\Periodos $periodicidad
     * @return OrigenDatos
     */
    public function setPeriodicidad(\MINSAL\IndicadoresBundle\Entity\Periodos $periodicidad = null)
    {
        $this->periodicidad = $periodicidad;

        return $this;
    }

    /**
     * Get periodicidad
     *
     * @return \MINSAL\IndicadoresBundle\Entity\Periodos
     */
    public function getPeriodicidad()
    {
        return $this->periodicidad;
    }

    /**
     * Add reporteActualizaciones
     *
     * @param \MINSAL\IndicadoresBundle\Entity\ReporteActualizacion $reporteActualizaciones
     * @return OrigenDatos
     */
    public function addReporteActualizaciones(\MINSAL\IndicadoresBundle\Entity\ReporteActualizacion $reporteActualizaciones) {
        $this->reporteActualizaciones[] = $reporteActualizaciones;

        return $this;
    }

    /**
     * Get reporteActualizaciones
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReporteActualizaciones() {
        return $this->reporteActualizaciones;
    }

    /**
     * Add fusiones
     *
     * @param  MINSAL\IndicadoresBundle\Entity\OrigenDatos $fusiones
     * @return OrigenDatos
     */
    public function addFusione(\MINSAL\IndicadoresBundle\Entity\OrigenDatos $fusiones)
    {
        $this->fusiones[] = $fusiones;

        return $this;
    }

    /**
     * Remove fusiones
     *
     * @param MINSAL\IndicadoresBundle\Entity\OrigenDatos $fusiones
     */
    public function removeFusione(\MINSAL\IndicadoresBundle\Entity\OrigenDatos $fusiones)
    {
        $this->fusiones->removeElement($fusiones);
    }

    /**
     * Get fusiones
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getFusiones()
    {
        return $this->fusiones;
    }

    /**
     * Add campos
     *
     * @param  MINSAL\IndicadoresBundle\Entity\Campo $campos
     * @return OrigenDatos
     */
    public function addCampo(\MINSAL\IndicadoresBundle\Entity\Campo $campos)
    {
        $this->campos[] = $campos;

        return $this;
    }

    /**
     * Remove campos
     *
     * @param MINSAL\IndicadoresBundle\Entity\Campo $campos
     */
    public function removeCampo(\MINSAL\IndicadoresBundle\Entity\Campo $campos)
    {
        $this->campos->removeElement($campos);
    }

    /**
     * Get campos
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCampos()
    {
        $campos = array();
        foreach ($this->campos as $campo) {
            if ($campo->getFormula() == null)
                $campos[] = $campo;
        }

        return $campos;
    }
    /**
     * Get camposCalculados
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCamposCalculados()
    {
        $campos = array();
        foreach ($this->campos as $campo) {
            if ($campo->getFormula() != null)
                $campos[] = $campo;
        }

        return $campos;
    }

    /**
     * Get AllFields
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getAllFields()
    {
        return $this->campos;
    }

    /**
     * Set id
     *
     * @param  integer     $id
     * @return OrigenDatos
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set esCatalogo
     *
     * @param  boolean     $esCatalogo
     * @return OrigenDatos
     */
    public function setEsCatalogo($esCatalogo)
    {
        $this->esCatalogo = $esCatalogo;

        return $this;
    }

    /**
     * Get esCatalogo
     *
     * @return boolean
     */
    public function getEsCatalogo()
    {
        return $this->esCatalogo;
    }
	
	/**
     * Set fechaCorte
     *
     * @param  date     $fechaCorte
     * @return OrigenDatos
     */
    public function setFechaCorte($fechaCorte)
    {
        $this->fechaCorte = $fechaCorte;

        return $this;
    }

    /**
     * Get esCatalogo
     *
     * @return date
     */
    public function getFechaCorte()
    {
        return $this->fechaCorte;
    }


    /**
     * Set nombreCatalogo
     *
     * @param  string      $nombreCatalogo
     * @return OrigenDatos
     */
    public function setNombreCatalogo($nombreCatalogo)
    {
        $this->nombreCatalogo = $nombreCatalogo;

        return $this;
    }

    /**
     * Get nombreCatalogo
     *
     * @return string
     */
    public function getNombreCatalogo()
    {
        return $this->nombreCatalogo;
    }

    /**
     * Add variables
     *
     * @param  \MINSAL\IndicadoresBundle\Entity\VariableDato $variables
     * @return OrigenDatos
     */
    public function addVariable(\MINSAL\IndicadoresBundle\Entity\VariableDato $variables)
    {
        $this->variables[] = $variables;

        return $this;
    }

    /**
     * Remove variables
     *
     * @param \MINSAL\IndicadoresBundle\Entity\VariableDato $variables
     */
    public function removeVariable(\MINSAL\IndicadoresBundle\Entity\VariableDato $variables)
    {
        $this->variables->removeElement($variables);
    }

    /**
     * Get variables
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Add conexiones
     *
     * @param  \MINSAL\IndicadoresBundle\Entity\Conexion $conexiones
     * @return OrigenDatos
     */
    public function addConexione(\MINSAL\IndicadoresBundle\Entity\Conexion $conexiones)
    {
        $this->conexiones[] = $conexiones;

        return $this;
    }

    /**
     * Remove conexiones
     *
     * @param \MINSAL\IndicadoresBundle\Entity\Conexion $conexiones
     */
    public function removeConexione(\MINSAL\IndicadoresBundle\Entity\Conexion $conexiones)
    {
        $this->conexiones->removeElement($conexiones);
    }

    /**
     * Get conexiones
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConexiones()
    {
        return $this->conexiones;
    }

    /**
     * Set esPivote
     *
     * @param  boolean     $esPivote
     * @return OrigenDatos
     */
    public function setEsPivote($esPivote)
    {
        $this->esPivote = $esPivote;

        return $this;
    }

    /**
     * Get esPivote
     *
     * @return boolean
     */
    public function getEsPivote()
    {
        return $this->esPivote;
    }


    /**
     * Add reporteActualizaciones
     *
     * @param \MINSAL\IndicadoresBundle\Entity\ReporteActualizacion $reporteActualizaciones
     * @return OrigenDatos
     */
    public function addReporteActualizacione(\MINSAL\IndicadoresBundle\Entity\ReporteActualizacion $reporteActualizaciones)
    {
        $this->reporteActualizaciones[] = $reporteActualizaciones;
    
        return $this;
    }

    /**
     * Remove reporteActualizaciones
     *
     * @param \MINSAL\IndicadoresBundle\Entity\ReporteActualizacion $reporteActualizaciones
     */
    public function removeReporteActualizacione(\MINSAL\IndicadoresBundle\Entity\ReporteActualizacion $reporteActualizaciones)
    {
        $this->reporteActualizaciones->removeElement($reporteActualizaciones);
    }	
	

	public function getUltimaLectura()
    { 
		global $kernel;
		if ( 'AppCache' == get_class($kernel) )
		{
		   $kernel = $kernel->getKernel();
		}
		$em = $kernel->getContainer()->get( 'doctrine.orm.entity_manager' );
	
		return $em->getRepository('IndicadoresBundle:OrigenDatos')->getUltimaActualizacionStatic($this->id);		 
	}
}