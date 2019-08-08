<?php

include_once 'koneksi.php';

function kmeans($data, $k)
{
        $cPositions = assign_initial_positions($data, $k);
        $clusters = array();
        while(true)
        {
                $changes = kmeans_clustering($data, $cPositions, $clusters);
                if(!$changes)
                {
                        return kmeans_get_cluster_values($clusters, $data);
                }
                $cPositions = kmeans_recalculate_cpositions($cPositions, $data, $clusters);
        }
}
function kmeans_clustering($data, $cPositions, &$clusters)
{
        $nChanges = 0;
        foreach($data as $dataKey => $value)
        {
                $minDistance = null;
                $cluster = null;
                foreach($cPositions as $k => $position)
                {
                        $distance = distance($value, $position);
                        if(is_null($minDistance) || $minDistance > $distance)
                        {
                                $minDistance = $distance;
                                $cluster = $k;
                        }
                }
                if(!isset($clusters[$dataKey]) || $clusters[$dataKey] != $cluster)
                {
                        $nChanges++;
                }
                $clusters[$dataKey] = $cluster;
        }
        return $nChanges;
}
function kmeans_recalculate_cpositions($cPositions, $data, $clusters)
{
        $kValues = kmeans_get_cluster_values($clusters, $data);
        foreach($cPositions as $k => $position)
        {
                $cPositions[$k] = empty($kValues[$k]) ? 0 : kmeans_avg($kValues[$k]);
        }
        return $cPositions;
}
function kmeans_get_cluster_values($clusters, $data)
{
        $values = array();
        foreach($clusters as $dataKey => $cluster)
        {
                $values[$cluster][] = $data[$dataKey];
        }
        return $values;
}
function kmeans_avg($values)
{
        $n = count($values);
        $sum = array_sum($values);
        return ($n == 0) ? 0 : $sum / $n;
}
function distance($v1, $v2)
{
  return abs($v1-$v2);
}
function assign_initial_positions($data, $k)
{
        $min = min($data);
        $max = max($data);
        $int = ceil(abs($max - $min) / $k);
        while($k-- > 0)
        {
                $cPositions[$k] = $min + $int * $k;
        }
        return $cPositions;
}

?>