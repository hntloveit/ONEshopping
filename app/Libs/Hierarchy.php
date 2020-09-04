<?php
namespace App\Libs;
use DB;
class Hierarchy {
    protected $pdo;
    /**
     * Hierarchy constructor.
     */
    public function __construct()
    {
        $this->pdo = DB::connection()->getPdo();
    }

    /**
     *
     * @add a node
     *
     * @access public
     *
     * @param string $left_node
     *
     * @param string $new_node
     *
     */
    public function addNodeOld($left_node, $data){
        $today = date('Y-m-d H:i:s');
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("SELECT @myRight := right_node FROM users WHERE id = :left_node");
            $stmt->bindParam(':left_node', $left_node);
            $stmt->execute();
            /*** increment the nodes by two ***/
            $this->pdo->exec("UPDATE users SET right_node = right_node + 2 WHERE right_node > @myRight");
            $this->pdo->exec("UPDATE users SET left_node = left_node + 2 WHERE left_node > @myRight");

            /*** insert the new node ***/
            $stmt = $this->pdo->prepare("INSERT INTO users(name, email, password, referral, mobile, address, grand_parent, level, created_at, updated_at, left_node, right_node) VALUES( :name, :email, :password, :referral, :phone, :address, :grand_parent, :level, :created_at, :updated_at, @myRight + 1, @myRight + 2)");
            $password = bcrypt($data['password']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':referral', $data['referral']);
            $stmt->bindParam(':mobile', $data['mobile']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':grand_parent', $data['grand_parent']);
            $stmt->bindParam(':level', $data['level']);
            $stmt->bindParam(':created_at', $today);
            $stmt->bindParam(':updated_at', $today);
            $stmt->execute();

            $user = DB::table('users')->where('email', $data['email'])->first();
			$stmt->bindParam(':user_id', $user->id);
            $stmt->execute();

            /*** commit the transaction ***/
            $this->pdo->commit();
            return true;
        }
        catch(Exception $e)
        {
            $this->pdo->rollBack();
            throw new Exception($e);
        }
    }	
	public function addNode($left_node, $data){
	$today = date('Y-m-d H:i:s');     
	try {           
	$this->pdo->beginTransaction();    
	$stmt = $this->pdo->prepare("SELECT @myRight := right_node FROM users WHERE id = $left_node");   
	$stmt->bindParam(':left_node', $left_node); 
	$stmt->execute();           
	/*** increment the nodes by two ***/		
	$id = $data['id'];			
	$parent = $data['referral'];	
	$grand_parent = $data['grand_parent'];	
	$level = $data['level'];		
	$role = $data['role'];  
	$this->pdo->exec("UPDATE users SET right_node = right_node + 2 WHERE right_node > @myRight");         
	$this->pdo->exec("UPDATE users SET left_node = left_node + 2 WHERE left_node > @myRight");			
	$this->pdo->exec("UPDATE users SET referral = $parent, grand_parent = $grand_parent, level =$level, left_node=@myRight + 1, right_node=@myRight + 2 WHERE id = $id");			
	$user = DB::table('users')->where('email', $data['email'])->first();    
	/*** commit the transaction ***/           
	$this->pdo->commit();           
	return true;        }        
	catch(Exception $e)        {  
	$this->pdo->rollBack();       

	throw new Exception($e);        }    }

    /**
     *
     * @Add child node
     * @ adds a child to a node that has no children
     *
     * @access public
     *
     * @param string $node_name The node to add to
     *
     * @param string $new_node The name of the new child node
     *
     * @return array
     *
     */
    public function addChildNode($node_id, $data){
        $today = date('Y-m-d H:i:s');
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("SELECT @myLeft := left_node FROM users WHERE id=$node_id");
            $stmt->bindParam(':node_id', $node_id);
            $stmt->execute();					
			$id = $data['id'];	
			$parent = $data['referral'];	
			$grand_parent = $data['grand_parent'];	
			$level = $data['level'];		
			$role = $data['role'];
            $this->pdo->exec("UPDATE users SET right_node = right_node + 2 WHERE right_node > @myLeft");
            $this->pdo->exec("UPDATE users SET left_node = left_node + 2 WHERE left_node > @myLeft");
            $this->pdo->exec("UPDATE users SET referral = $parent, grand_parent = $grand_parent, level =$level, left_node=@myLeft + 1, right_node=@myLeft + 2 WHERE id = $id");
			
            $user = DB::table('users')->where('email', $data['email'])->first();


            $this->pdo->commit();
        }
        catch(Exception $e)
        {
            $this->pdo->rollBack();
            throw new Exception($e);
        }
    }

    /**
     *
     * Retrieve a depth of nodes
     *
     * @access public
     *
     * @param $node_name
     *
     * @return array
     *
     */
    public function getNodeDepth(){
        $stmt = $this->pdo->prepare("SELECT node.id, (COUNT(parent.id) - 1) AS depth FROM users AS node, users AS parent WHERE node.left_node BETWEEN parent.left_node AND parent.right_node GROUP BY node.name ORDER BY node.left_node");
        $stmt->execute();
        return $stmt->fetchALL(\PDO::FETCH_ASSOC);
    }

    /**
     *
     * Retrieve a single path
     *
     * @access public
     *
     * @param $node_name
     *
     * @return array
     *
     */
    public function singlePath($node_id){
        $stmt = $this->pdo->prepare("SELECT parent.id FROM users AS node, users AS parent WHERE node.left_node BETWEEN parent.left_node AND parent.right_node AND node.id = $node_id ORDER BY parent.left_node DESC LIMIT 6");
        $stmt->execute();
        return $stmt->fetchALL(\PDO::FETCH_ASSOC);
    }
	public function singlePathNewbie($node_id){
        $stmt = $this->pdo->prepare("SELECT referral as id FROM users WHERE id = $node_id");
        $stmt->execute();
        return $stmt->fetchALL(\PDO::FETCH_ASSOC);
    }

    /***
     *
     * @fetch the full tree
     *
     * @param string $parent
     *
     * @return array
     *
     */
    public function fullTree($parent){
        $stmt = $this->pdo->prepare("SELECT node.id, node.level, node.parent, node.id_card FROM users AS node, users AS parent WHERE node.left_node BETWEEN parent.left_node AND parent.right_node AND parent.id = :parent ORDER BY node.left_node");
        $stmt->bindParam('parent', $parent);
        $stmt->execute();
        $res = $stmt->fetchALL(\PDO::FETCH_ASSOC);
        return $res;
    }
	public function fullTreeNewbie(){
        $stmt = $this->pdo->prepare("SELECT id, referral, id_card, name, level-1 AS depth FROM users WHERE role <> 'sale' ORDER BY left_node");
        $stmt->execute();
        $res = $stmt->fetchALL(\PDO::FETCH_ASSOC);
        return $res;
    }

    /**
     *
     * Retrieve a subTree depth
     *
     * @access public
     *
     * @param $node_name
     *
     * @return array
     *
     */
    public function subTreeDepth($parent){
        $stmt = $this->pdo->prepare("SELECT node.id, (COUNT(parent.id) - 1) AS depth FROM users AS node, users AS parent WHERE node.left_node BETWEEN parent.left_node AND parent.right_node AND node.id = '{$parent}' GROUP BY node.id ORDER BY node.left_node");
        $stmt->execute();
        return $stmt->fetchALL(\PDO::FETCH_ASSOC);
    }

    /**
     *
     * @fetch local sub nodes only
     *
     * @access public
     *
     * @param $node_name
     *
     * @return array
     *
     */
    public function getLocalSubNodes($node_id,$depth){
        if($depth == 0) {
            $stmt = $this->pdo->prepare(" SELECT node.id, node.parent, node.id_card, node.name, (COUNT(parent.id) - (sub_tree.depth + 1)) AS depth FROM users AS node, users AS parent, users AS sub_parent,
        (
        SELECT node.id, (COUNT(parent.id) - 1) AS depth
        FROM users AS node,
        users AS parent
        WHERE node.left_node BETWEEN parent.left_node AND parent.right_node
        AND node.id = :node_id
        GROUP BY node.id
        ORDER BY node.left_node
        )AS sub_tree
WHERE node.left_node BETWEEN parent.left_node AND parent.right_node
AND node.left_node BETWEEN sub_parent.left_node AND sub_parent.right_node
AND sub_parent.id = sub_tree.id
GROUP BY node.id
ORDER BY node.left_node");
        }else {
            $stmt = $this->pdo->prepare(" SELECT node.id, node.parent, node.id_card, node.name, (COUNT(parent.id) - (sub_tree.depth + 1)) AS depth FROM users AS node, users AS parent, users AS sub_parent,
        (
        SELECT node.id, (COUNT(parent.id) - 1) AS depth
        FROM users AS node,
        users AS parent
        WHERE node.left_node BETWEEN parent.left_node AND parent.right_node
        AND node.id = :node_id
        GROUP BY node.id
        ORDER BY node.left_node
        )AS sub_tree
WHERE node.left_node BETWEEN parent.left_node AND parent.right_node
AND node.left_node BETWEEN sub_parent.left_node AND sub_parent.right_node
AND sub_parent.id = sub_tree.id
GROUP BY node.id
HAVING depth <= $depth
ORDER BY node.left_node");
        }
        $stmt->bindParam(':node_id', $node_id, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchALL(\PDO::FETCH_ASSOC);
    }

    public function leafNodes(){
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE right_node = left_node + 1");
        $stmt->execute();
        return $stmt->fetchALL(\PDO::FETCH_ASSOC);
    }
}