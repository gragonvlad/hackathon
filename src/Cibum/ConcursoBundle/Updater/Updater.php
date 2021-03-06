<?php
namespace Cibum\ConcursoBundle\Updater;

use Doctrine\ORM\EntityManager;
use Cibum\ConcursoBundle\Entity\Proyecto;
use Cibum\ConcursoBundle\Entity\ProyectoRepository;
use Cibum\ConcursoBundle\Entity\Anual;
use Cibum\ConcursoBundle\Entity\Distrito;
use Socrata;

class Updater
{
    /** @var $em \Doctrine\ORM\EntityManager */
    private $em;

    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function batchUpdate()
    {
        $socrata = new Socrata('https://opendata.socrata.com/api');

        //pull data
        $data = $socrata->get('/views/h3ut-rsd9/rows.json', array('meta' => 'false'));
        $data = $data['data'];

        $data = array_map(function ($item) {
            return array_map(function ($it) {
                return trim($it);
            }, $item);
        }, $data);

        $datasimple = array();
        foreach ($data as $row) {
            if ($row[11] != "")
                $datasimple[] = $row[8] . ':' . $row[11];
        }

        /** @var $repo ProyectoRepository */
        $repo = $this->em->getRepository('CibumConcursoBundle:Proyecto');

        $actualproj = $repo->getAllQuick();

        $new = array_values(array_diff($datasimple, $actualproj));

        $datavalid = array();
        $i = 0;
        $tam = count($new);

        foreach ($data as $row) {
            if ($i === $tam)
                break;
            $pair = $row[8] . ':' . $row[11];
            if ($pair === $new[$i]) {
                $datavalid[] = $row;
                $i++;
            }
        }

        $updates = 0;

        $cacheddistritos = array();
        foreach ($datavalid as $fila) {

            $project = $repo->findOneBy(array('snip' => $fila[11]));
            if (!$project) {
                $project = new Proyecto();
                $project->setNombre($fila[9]);
                $project->setDescripcion($fila[10]);
                $project->setSnip($fila[11]);
                $project->setSiaf($fila[12]);
                $project->setLatitud($fila[26]);
                $project->setLongitud($fila[27]);

                $updates++;
            }

            $anho = new Anual();
            $anho->setAnho($fila[8]);
            $anho->setEstado($fila[13]);
            $anho->setPresupuesto((int)$fila[15]);
            $anho->setPia((int)$fila[16]);
            $anho->setPim((int)$fila[17]);
            $anho->setEjecucionAcumulada((float)$fila[22]);
            $anho->setAvance((float)$fila[23]);
            $updates++;

            $distritos = explode(',', $fila[14]);

            foreach ($distritos as $distrito) {
                $distritoObj = null;
                $distNombre = trim($distrito);
                if (isset($cacheddistritos[$distrito])) {
                    $distritoObj = $cacheddistritos[$distrito];
                } else {
                    $distritoObj = $this->em->getRepository('CibumConcursoBundle:Distrito')->findOneBy(array('nombre' => $distNombre));
                    if (!$distritoObj) {
                        $distritoObj = new Distrito();
                        $distritoObj->setNombre($distNombre);
                        $this->em->persist($distritoObj);
                        $updates++;
                    }
                    $cacheddistritos[$distrito] = $distritoObj;
                }

                $anho->addDistrito($distritoObj);
            }
            $this->em->persist($anho);

            $project->addAnual($anho);
            $this->em->persist($project);
            $this->em->flush();
        }

        return $updates;
    }

    public function updateOne($project)
    {

    }

}
